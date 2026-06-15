<?php

class WritingModel {
    private $dataPath;
    
    public function __construct() {
        $this->dataPath = __DIR__ . '/../../data/writing/';
    }
    
    /**
     * Get list of all available test IDs by scanning filenames (fast, no file reading)
     */
    public function getAvailableTestIds() {
        if (!is_dir($this->dataPath)) {
            return [];
        }
        
        $files = glob($this->dataPath . '*.txt');
        $testIds = [];
        
        foreach ($files as $file) {
            $filename = basename($file);
            if (preg_match('/TEST\s+(\d+)/i', $filename, $matches)) {
                $testIds[] = (int)$matches[1];
            } else {
                // Fallback: quickly read first line to check for [TEST X - PART Y]
                $handle = fopen($file, 'r');
                if ($handle) {
                    $line = fgets($handle);
                    if ($line !== false && preg_match('/\[TEST\s+(\d+)/i', $line, $matches)) {
                        $testIds[] = (int)$matches[1];
                    }
                    fclose($handle);
                }
            }
        }
        
        $testIds = array_unique($testIds);
        sort($testIds);
        return $testIds;
    }
    
    /**
     * Load all writing tests. Optimized to only parse the requested test.
     */
    public function getAllTests($activeTestId = null) {
        $testIds = $this->getAvailableTestIds();
        if (empty($testIds)) {
            return [];
        }
        
        $allTests = [];
        // Initialize placeholders for all available tests to populate the sidebar links
        foreach ($testIds as $id) {
            $allTests[$id] = ['id' => $id, 'parts' => []];
        }
        
        // If no active test ID is specified, default to the first available test
        if ($activeTestId === null && !empty($testIds)) {
            $activeTestId = $testIds[0];
        }
        
        if ($activeTestId !== null && in_array($activeTestId, $testIds)) {
            // Find the file corresponding to the active test ID
            $targetFile = null;
            $files = glob($this->dataPath . '*.txt');
            foreach ($files as $file) {
                if (preg_match('/TEST\s+' . $activeTestId . '\b/i', basename($file))) {
                    $targetFile = $file;
                    break;
                }
            }
            
            // Fallback if filename pattern didn't match: parse files to find the right one
            if (!$targetFile) {
                foreach ($files as $file) {
                    $handle = fopen($file, 'r');
                    if ($handle) {
                        $line = fgets($handle);
                        if ($line !== false && preg_match('/\[TEST\s+' . $activeTestId . '\s*-\s*PART\s+\d+\]/i', $line)) {
                            $targetFile = $file;
                            fclose($handle);
                            break;
                        }
                        fclose($handle);
                    }
                }
            }
            
            if ($targetFile) {
                $testData = $this->parseWritingFile($targetFile);
                if ($testData) {
                    $allTests[$activeTestId] = $testData;
                }
            }
        }
        
        return $allTests;
    }
    
    /**
     * Get specific test by ID
     */
    public function getTest($testId) {
        $allTests = $this->getAllTests($testId);
        return $allTests[$testId] ?? null;
    }
    
    /**
     * Parse writing test file using a robust state-machine parser
     */
    private function parseWritingFile($filePath) {
        if (!file_exists($filePath)) {
            return null;
        }
        
        $content = file_get_contents($filePath);
        // Normalize line endings
        $content = str_replace("\r\n", "\n", $content);
        $lines = explode("\n", $content);
        
        $testId = null;
        $parts = [];
        $currentPart = null;
        $currentQuestion = null;
        
        $accumulatingType = null; // 'question_answer' or 'sample_answer'
        $accumulatingBuffer = [];
        
        // Helper to save current question
        $saveCurrentQuestion = function() use (&$currentQuestion, &$currentPart, &$accumulatingType, &$accumulatingBuffer) {
            if ($currentQuestion && $currentPart) {
                if ($accumulatingType === 'question_answer') {
                    $currentQuestion['answer'] = trim(implode("\n", $accumulatingBuffer));
                }
                $currentPart['questions'][] = $currentQuestion;
            }
            $currentQuestion = null;
            $accumulatingType = null;
            $accumulatingBuffer = [];
        };
        
        // Helper to save current part
        $saveCurrentPart = function() use (&$currentPart, &$parts, $saveCurrentQuestion, &$accumulatingType, &$accumulatingBuffer) {
            if ($currentPart) {
                $saveCurrentQuestion();
                if ($accumulatingType === 'sample_answer') {
                    $currentPart['sample_answer'] = trim(implode("\n", $accumulatingBuffer));
                }
                $parts[] = $currentPart;
            }
            $currentPart = null;
            $accumulatingType = null;
            $accumulatingBuffer = [];
        };
        
        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            
            // Check for [TEST X - PART Y]
            if (preg_match('/\[TEST\s+(\d+)\s*-\s*PART\s+(\d+)\]/i', $trimmedLine, $matches)) {
                $saveCurrentPart();
                
                $testId = (int)$matches[1];
                $partId = (int)$matches[2];
                
                $currentPart = [
                    'id' => $partId,
                    'type' => ($partId === 1) ? 'sentence_building' : 'writing_task',
                    'context' => '',
                    'questions' => [],
                    'sample_answer' => ''
                ];
                continue;
            }
            
            // Match Type: ...
            if (preg_match('/^Type:\s*(.+)$/i', $trimmedLine, $matches)) {
                if ($currentPart) {
                    $type = strtolower(trim($matches[1]));
                    $currentPart['type'] = ($type === 'sentence building') ? 'sentence_building' : 'writing_task';
                }
                continue;
            }
            
            // Match Context: ...
            if (preg_match('/^Context:\s*(.+)$/i', $trimmedLine, $matches)) {
                if ($currentPart) {
                    $currentPart['context'] = trim($matches[1]);
                }
                continue;
            }
            
            // Match Question X: ...
            if (preg_match('/^Question\s+(\d+):\s*(.+)$/i', $trimmedLine, $matches)) {
                $saveCurrentQuestion();
                
                $questionId = (int)$matches[1];
                $questionText = trim($matches[2]);
                
                $currentQuestion = [
                    'id' => $questionId,
                    'text' => $questionText,
                    'answer' => ''
                ];
                continue;
            }
            
            // Match Answer: ...
            if (preg_match('/^Answer:\s*(.*)$/i', $trimmedLine, $matches)) {
                // Save previous question if there was one (that wasn't this one)
                // In normal formats, Answer comes right after the matching Question, so $currentQuestion is active.
                $accumulatingType = 'question_answer';
                $accumulatingBuffer = [];
                $val = trim($matches[1]);
                if ($val !== '') {
                    $accumulatingBuffer[] = $val;
                }
                continue;
            }
            
            // Match Sample Answer: ...
            if (preg_match('/^Sample Answer:\s*(.*)$/i', $trimmedLine, $matches)) {
                $saveCurrentQuestion();
                $accumulatingType = 'sample_answer';
                $accumulatingBuffer = [];
                $val = trim($matches[1]);
                if ($val !== '') {
                    $accumulatingBuffer[] = $val;
                }
                continue;
            }
            
            // Accumulate lines if we are inside an answer block
            if ($accumulatingType !== null) {
                // If it is a new section starting or an empty line when we don't need it, we handle it.
                // We keep original spacing of multi-line answers by rtrimming the line.
                $accumulatingBuffer[] = rtrim($line);
            }
        }
        
        // Save final part
        $saveCurrentPart();
        
        if (!$testId) {
            return null;
        }
        
        return [
            'id' => $testId,
            'parts' => $parts
        ];
    }
}

<?php

class TestModel {
    private $dataPath;
    
    public function __construct() {
        $this->dataPath = __DIR__ . '/../../data/';
    }
    
    /**
     * Get list of all available test IDs by scanning filenames (fast, no file reading)
     */
    public function getAvailableTestIds($skill) {
        $skillPath = $this->dataPath . $skill . '/';
        if (!is_dir($skillPath)) {
            return [];
        }
        
        $files = glob($skillPath . '*.txt');
        $testIds = [];
        
        foreach ($files as $file) {
            $filename = basename($file);
            // Match "TEST <number>" in filename (e.g. "[TEST 1 ].txt")
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
     * Load tests for a specific skill. Optimized to only parse the requested test.
     */
    public function getTestData($skill, $activeTestId = null) {
        $testIds = $this->getAvailableTestIds($skill);
        if (empty($testIds)) {
            return [];
        }
        
        $allTests = [];
        // Initialize placeholders for all available tests to populate the sidebar links
        foreach ($testIds as $id) {
            $allTests[$id] = [];
        }
        
        // If no active test ID is specified, default to the first available test
        if ($activeTestId === null && !empty($testIds)) {
            $activeTestId = $testIds[0];
        }
        
        if ($activeTestId !== null && in_array($activeTestId, $testIds)) {
            // Find the file corresponding to the active test ID
            $skillPath = $this->dataPath . $skill . '/';
            $targetFile = null;
            
            // Look for file containing "TEST <id>"
            $files = glob($skillPath . '*.txt');
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
                $testData = $this->parseTestFile($targetFile);
                if (isset($testData[$activeTestId])) {
                    $allTests[$activeTestId] = $testData[$activeTestId];
                }
            }
        }
        
        return $allTests;
    }
    
    /**
     * Parse a single test file
     */
    private function parseTestFile($filePath) {
        if (!file_exists($filePath)) {
            return [];
        }
        
        $content = file_get_contents($filePath);
        $lines = explode("\n", $content);
        
        $tests = [];
        $currentTestId = null;
        $currentPartId = null;
        $currentQuestion = null;
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (empty($line)) {
                continue;
            }
            
            // Match [TEST X - PART Y]
            if (preg_match('/\[TEST\s+(\d+)\s*-\s*PART\s+(\d+)\]/i', $line, $matches)) {
                // If we had a parsed question, save it to the previous part
                if ($currentTestId && $currentPartId && $currentQuestion) {
                    $tests[$currentTestId][$currentPartId]['questions'][$currentQuestion['id']] = $currentQuestion;
                }
                
                $currentTestId = (int)$matches[1];
                $currentPartId = (int)$matches[2];
                
                if (!isset($tests[$currentTestId])) {
                    $tests[$currentTestId] = [];
                }
                
                $tests[$currentTestId][$currentPartId] = [
                    'title' => 'TEST ' . $currentTestId . ' - PART ' . $currentPartId,
                    'type' => 'multiple_choice',
                    'context' => '',
                    'questions' => []
                ];
                
                $currentQuestion = null;
                continue;
            }
            
            // Match Type: ...
            if (preg_match('/^Type:\s*(.+)$/i', $line, $matches)) {
                if ($currentTestId && $currentPartId) {
                    $type = strtolower(trim($matches[1]));
                    $tests[$currentTestId][$currentPartId]['type'] = ($type === 'fill in the blank' || $type === 'matching') ? 'fill_in' : 'multiple_choice';
                }
                continue;
            }
            
            // Match Audio: ...
            if (preg_match('/^Audio:\s*(.+)$/i', $line, $matches)) {
                if ($currentTestId && $currentPartId) {
                    $tests[$currentTestId][$currentPartId]['audio'] = trim($matches[1]);
                }
                continue;
            }
            
            // Match Context: ...
            if (preg_match('/^Context:\s*(.+)$/i', $line, $matches)) {
                if ($currentTestId && $currentPartId) {
                    $tests[$currentTestId][$currentPartId]['context'] = trim($matches[1]);
                }
                continue;
            }
            
            // Match Question X: ...
            if (preg_match('/^Question\s+(\d+):\s*(.+)$/i', $line, $matches)) {
                // Save previous question before starting new one
                if ($currentTestId && $currentPartId && $currentQuestion) {
                    $tests[$currentTestId][$currentPartId]['questions'][$currentQuestion['id']] = $currentQuestion;
                }
                
                $questionId = (int)$matches[1];
                $questionText = trim($matches[2]);
                
                $currentQuestion = [
                    'id' => $questionId,
                    'text' => $questionText,
                    'options' => [],
                    'correct' => ''
                ];
                continue;
            }
            
            // Match Answer: ...
            if (preg_match('/^Answer:\s*(.+)$/i', $line, $matches)) {
                if ($currentQuestion) {
                    $currentQuestion['correct'] = trim($matches[1]);
                }
                continue;
            }
            
            // Match Options A. B. C. D.
            if (preg_match('/^([A-D])\.\s*(.+)$/i', $line, $matches)) {
                if ($currentQuestion) {
                    $optionKey = strtoupper($matches[1]);
                    $optionValue = trim($matches[2]);
                    $currentQuestion['options'][$optionKey] = $optionValue;
                }
                continue;
            }
            
            // Match Correct: ...
            if (preg_match('/^Correct:\s*(.+)$/i', $line, $matches)) {
                if ($currentQuestion) {
                    $currentQuestion['correct'] = trim($matches[1]);
                }
                continue;
            }
        }
        
        // Save the final question of the file
        if ($currentTestId && $currentPartId && $currentQuestion) {
            $tests[$currentTestId][$currentPartId]['questions'][$currentQuestion['id']] = $currentQuestion;
        }
        
        // Ensure questions inside each part are sorted numerically by their ID keys
        foreach ($tests as $tId => &$tData) {
            foreach ($tData as $pId => &$pData) {
                if (!empty($pData['questions'])) {
                    ksort($pData['questions']);
                }
            }
        }
        
        return $tests;
    }
}


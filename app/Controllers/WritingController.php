<?php

require_once __DIR__ . '/../Models/WritingModel.php';

class WritingController {
    public function index($testId) {
        $writingModel = new WritingModel();
        $allTests = $writingModel->getAllTests();
        $testData = $writingModel->getTest($testId);

        $viewData = [
            'testId' => $testId,
            'testData' => $testData,
            'allTests' => $allTests,
            'results' => null,
            'userAnswers' => []
        ];

        $this->renderView($viewData);
    }

    public function submit($testId) {
        $writingModel = new WritingModel();
        $allTests = $writingModel->getAllTests($testId);
        $testData = $writingModel->getTest($testId);

        $userAnswers = $_POST['answers'] ?? [];
        $results = [];
        $score = 0;
        $totalQuestions = 0;

        if ($testData && !empty($testData['parts'])) {
            foreach ($testData['parts'] as $part) {
                // Only grade Part 1 (sentence building)
                if ($part['type'] === 'sentence_building') {
                    foreach ($part['questions'] as $question) {
                        $qId = $question['id'];
                        $totalQuestions++;
                        
                        $correctAnswer = trim($question['answer']);
                        $userAnswer = trim($userAnswers[$qId] ?? '');
                        
                        // Improved comparison
                        $isCorrect = $this->compareAnswers($userAnswer, $correctAnswer);
                        
                        if ($isCorrect) {
                            $score++;
                        }
                        
                        $results[$qId] = [
                            'correct' => $isCorrect,
                            'correct_answer' => $correctAnswer,
                            'user_answer' => $userAnswer
                        ];
                    }
                }
            }
        }

        $viewData = [
            'testId' => $testId,
            'testData' => $testData,
            'allTests' => $allTests,
            'results' => [
                'score' => $score,
                'total' => $totalQuestions,
                'details' => $results
            ],
            'userAnswers' => $userAnswers
        ];

        $this->renderView($viewData);
    }
    
    /**
     * Compare user answer with correct answer
     * More flexible comparison for sentence building
     */
    private function compareAnswers($userAnswer, $correctAnswer) {
        // If user answer is empty, it's wrong
        if (empty(trim($userAnswer))) {
            return false;
        }
        
        // Normalize both answers
        $user = $this->normalizeAnswer($userAnswer);
        $correct = $this->normalizeAnswer($correctAnswer);
        
        // If normalized user answer is empty, it's wrong
        if (empty($user)) {
            return false;
        }
        
        // Exact match after normalization
        if ($user === $correct) {
            return true;
        }
        
        // Check similarity (allow minor differences like punctuation)
        similar_text($user, $correct, $percent);
        
        // If similarity is >= 95%, consider it correct
        return $percent >= 95;
    }
    
    /**
     * Normalize answer for comparison
     */
    private function normalizeAnswer($answer) {
        // Convert to lowercase
        $answer = strtolower($answer);
        
        // Remove extra spaces
        $answer = preg_replace('/\s+/', ' ', $answer);
        
        // Remove trailing punctuation for comparison
        $answer = rtrim($answer, '.,!?;:');
        
        // Trim
        $answer = trim($answer);
        
        return $answer;
    }
    
    private function renderView($data) {
        extract($data);
        require_once __DIR__ . '/../Views/layout/header.php';
        require_once __DIR__ . '/../Views/writing/index.php';
        require_once __DIR__ . '/../Views/layout/footer.php';
    }
}

<?php

require_once __DIR__ . '/../Models/TestModel.php';

class TestController {
    public function index($skill, $testId) {
        $testModel = new TestModel();
        // Pass $testId for performance optimization
        $allTests = $testModel->getTestData($skill, $testId);
        $testData = isset($allTests[$testId]) ? $allTests[$testId] : null;

        $viewData = [
            'skill' => $skill,
            'testId' => $testId,
            'testData' => $testData,
            'allTests' => $allTests,
            'results' => null,
            'userAnswers' => []
        ];

        $this->renderView($viewData);
    }

    public function submit($skill, $testId) {
        $testModel = new TestModel();
        // Pass $testId for performance optimization
        $allTests = $testModel->getTestData($skill, $testId);
        $testData = isset($allTests[$testId]) ? $allTests[$testId] : null;

        $userAnswers = $_POST['answers'] ?? [];
        $results = [];
        $score = 0;
        $totalQuestions = 0;

        if ($testData) {
            foreach ($testData as $part) {
                $isMultipleChoice = ($part['type'] === 'multiple_choice');
                foreach ($part['questions'] as $qId => $question) {
                    $totalQuestions++;
                    $correctAnswer = $question['correct'];
                    $userAnswer = $userAnswers[$qId] ?? '';

                    $isCorrect = $this->compareAnswers($userAnswer, $correctAnswer, $isMultipleChoice);
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

        $viewData = [
            'skill' => $skill,
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
     * Smart comparison for answers
     */
    private function compareAnswers($userAnswer, $correctAnswer, $isMultipleChoice) {
        $userAnswer = trim($userAnswer);
        $correctAnswer = trim($correctAnswer);
        
        if ($userAnswer === '') {
            return false;
        }
        
        if ($isMultipleChoice) {
            // Multiple choice requires exact option match (A, B, C or D), case-insensitive
            return strcasecmp($userAnswer, $correctAnswer) === 0;
        }
        
        // For fill-in-the-blank, support multiple options separated by / or |
        $alternatives = preg_split('/\s*[\/|]\s*/', $correctAnswer);
        
        foreach ($alternatives as $alt) {
            $user = $this->normalizeAnswer($userAnswer);
            $correct = $this->normalizeAnswer($alt);
            
            if ($user === $correct) {
                return true;
            }
            
            // Check similarity (allow minor differences like punctuation or tiny typos)
            similar_text($user, $correct, $percent);
            if ($percent >= 95) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Normalize answer string
     */
    private function normalizeAnswer($answer) {
        $answer = strtolower($answer);
        // Remove duplicate spacing
        $answer = preg_replace('/\s+/', ' ', $answer);
        // Strip trailing punctuation
        $answer = rtrim($answer, '.,!?;:');
        return trim($answer);
    }
    
    private function renderView($data) {
        extract($data);
        require_once __DIR__ . '/../Views/layout/header.php';
        require_once __DIR__ . '/../Views/test/index.php';
        require_once __DIR__ . '/../Views/layout/footer.php';
    }
}

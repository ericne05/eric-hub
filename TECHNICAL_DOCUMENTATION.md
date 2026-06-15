# Technical Documentation

## Architecture Overview

This application follows the **MVC (Model-View-Controller)** pattern without a database, using flat text files for data storage.

```
┌─────────────┐
│   Browser   │
└──────┬──────┘
       │ HTTP Request
       ↓
┌─────────────────────────────────┐
│  public/index.php (Router)      │
│  - Parses GET parameters        │
│  - Routes to Controller         │
└──────┬──────────────────────────┘
       │
       ↓
┌─────────────────────────────────┐
│  TestController.php             │
│  - index(): Display test        │
│  - submit(): Grade answers      │
└──────┬──────────────────────────┘
       │
       ├──→ ┌──────────────────────┐
       │    │  TestModel.php       │
       │    │  - Load test files   │
       │    │  - Parse content     │
       │    └──────────────────────┘
       │
       └──→ ┌──────────────────────┐
            │  Views/test/index.php│
            │  - Render UI         │
            │  - Show results      │
            └──────────────────────┘
```

## Component Details

### 1. Router (public/index.php)

**Purpose**: Entry point for all requests

**Responsibilities**:
- Parse URL parameters (`skill`, `test`)
- Instantiate TestController
- Route to appropriate method (GET → index, POST → submit)

**Code Flow**:
```php
GET  /?skill=listening&test=1  → controller->index()
POST /?skill=listening&test=1  → controller->submit()
```

### 2. Model (app/Models/TestModel.php)

**Purpose**: Data layer - loads and parses test files

**Key Methods**:

#### `getTestData($skill)`
- Scans `data/{skill}/` directory
- Finds ALL `.txt` files using `glob()`
- Calls `parseTestFile()` for each file
- Merges all tests into a single array
- Returns: `Array[testId][partId] => [questions, context, type]`

#### `parseTestFile($filePath)`
- Reads file content line by line
- Uses regex patterns to identify:
  - Test headers: `/\[TEST\s+(\d+)\s*-\s*PART\s+(\d+)\]/i`
  - Type declarations: `/^Type:\s*(.+)$/i`
  - Context: `/^Context:\s*(.+)$/i`
  - Questions: `/^Question\s+(\d+):\s*(.+)$/i`
  - Options: `/^([A-D])\.\s*(.+)$/i`
  - Correct answers: `/^Correct:\s*(.+)$/i`
- Returns structured array

**Data Structure**:
```php
[
    1 => [  // Test ID
        1 => [  // Part ID
            'title' => 'TEST 1 - PART 1',
            'type' => 'multiple_choice',  // or 'fill_in'
            'context' => 'Context text...',
            'questions' => [
                1 => [
                    'id' => 1,
                    'text' => 'Question text?',
                    'options' => ['A' => 'Option A', 'B' => 'Option B'],
                    'correct' => 'A'
                ]
            ]
        ]
    ]
]
```

### 3. Controller (app/Controllers/TestController.php)

**Purpose**: Business logic - handles requests and coordinates Model/View

**Key Methods**:

#### `index($skill, $testId)`
- Loads test data from Model
- Prepares view data
- Renders view (no grading)

#### `submit($skill, $testId)`
- Loads test data from Model
- Retrieves user answers from `$_POST['answers']`
- Grades each answer:
  - Normalizes strings (lowercase, trim)
  - Compares user answer with correct answer
  - Case-insensitive comparison
- Calculates score
- Prepares results array
- Renders view with results

**Grading Logic**:
```php
foreach ($testData as $part) {
    foreach ($part['questions'] as $qId => $question) {
        $userAnswer = $_POST['answers'][$qId] ?? '';
        $correctAnswer = $question['correct'];
        
        $isCorrect = (strcasecmp(trim($userAnswer), trim($correctAnswer)) == 0);
        
        $results[$qId] = [
            'correct' => $isCorrect,
            'correct_answer' => $correctAnswer
        ];
    }
}
```

### 4. View (app/Views/test/index.php)

**Purpose**: Presentation layer - renders HTML

**Layout Structure**:
```html
<div class="container">
    <div class="sidebar">
        <!-- Skills menu -->
        <!-- Tests menu -->
    </div>
    
    <div class="main-content">
        <form method="POST">
            <!-- Score summary (if submitted) -->
            
            <!-- For each part -->
            <div class="part-container">
                <!-- For each question -->
                <div class="question-box [correct-answer|wrong-answer]">
                    <!-- Question text -->
                    
                    <!-- If multiple choice: radio buttons -->
                    <!-- If fill-in: text input -->
                    
                    <!-- If wrong: show correct answer -->
                </div>
            </div>
            
            <button type="submit">Submit Answers</button>
        </form>
    </div>
</div>
```

**Dynamic Elements**:
- Active menu highlighting: `class="<?php echo ($skill === 'listening') ? 'active' : ''; ?>"`
- Result highlighting: `class="question-box <?php echo $resultClass; ?>"`
- Preserved answers: `value="<?php echo htmlspecialchars($userAnswers[$qId] ?? ''); ?>"`

### 5. Styling (public/css/style.css)

**Key CSS Classes**:

- `.sidebar`: Fixed 20% width, dark background
- `.main-content`: 80% width, offset by sidebar
- `.part-container`: White card with shadow
- `.question-box`: Default gray border
- `.correct-answer`: Green background + border
- `.wrong-answer`: Red background + border
- `.correct-answer-text`: Yellow background with red border (for showing correct answer)

**Responsive Design**:
```css
@media (max-width: 768px) {
    .sidebar { width: 100%; position: relative; }
    .main-content { width: 100%; margin-left: 0; }
}
```

## Data Flow

### GET Request (Display Test)
```
1. User clicks "Test 1"
2. Browser: GET /?skill=listening&test=1
3. Router: new TestController()->index('listening', 1)
4. Controller: $model->getTestData('listening')
5. Model: Scans data/listening/*.txt, parses, returns array
6. Controller: Passes data to View
7. View: Renders form with questions
8. Browser: Displays test
```

### POST Request (Submit Answers)
```
1. User clicks "Submit Answers"
2. Browser: POST /?skill=listening&test=1 with form data
3. Router: new TestController()->submit('listening', 1)
4. Controller: $model->getTestData('listening')
5. Controller: Grades $_POST['answers'] against correct answers
6. Controller: Calculates score, creates results array
7. Controller: Passes data + results to View
8. View: Renders form with color-coded results
9. Browser: Displays test with green/red highlighting
```

## File Parsing Algorithm

**Input**: Text file with test data
**Output**: Structured PHP array

**Algorithm**:
```
1. Read file line by line
2. Initialize: currentTestId, currentPartId, currentQuestion = null
3. For each line:
   a. If matches [TEST X - PART Y]:
      - Set currentTestId = X, currentPartId = Y
      - Initialize part structure
   b. If matches "Type: ...":
      - Set part type (multiple_choice or fill_in)
   c. If matches "Context: ...":
      - Set part context
   d. If matches "Question N: ...":
      - Save previous question (if exists)
      - Initialize new question with id=N, text=...
   e. If matches "A. ..." (or B, C, D):
      - Add option to current question
   f. If matches "Correct: ...":
      - Set correct answer for current question
4. Return structured array
```

## Security Considerations

1. **Input Sanitization**:
   - All output uses `htmlspecialchars()` to prevent XSS
   - GET parameters are cast to appropriate types

2. **File Access**:
   - Only reads from predefined `data/` directory
   - No user-controlled file paths

3. **Form Handling**:
   - POST data is validated before processing
   - No SQL injection risk (no database)

## Performance Optimization

1. **File Caching**: Could add caching layer for parsed test data
2. **Lazy Loading**: Currently loads all tests; could load on-demand
3. **Minification**: CSS/JS could be minified for production

## Extension Points

1. **Add Database**: Replace TestModel with database queries
2. **User Authentication**: Add login system to track progress
3. **Progress Tracking**: Store user scores and history
4. **Timer**: Add countdown timer for timed tests
5. **Randomization**: Shuffle questions/options for practice
6. **Export Results**: Generate PDF reports of scores

## Testing Checklist

- [ ] All 5 listening tests load correctly
- [ ] Reading and writing tests load
- [ ] Multiple choice questions display radio buttons
- [ ] Fill-in-the-blank questions display text inputs
- [ ] Correct answers highlight green
- [ ] Wrong answers highlight red and show correct answer
- [ ] Score calculation is accurate
- [ ] User answers are preserved after submission
- [ ] Sidebar navigation works
- [ ] Responsive design works on mobile

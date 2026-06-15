# Usage Guide - English Exam Memorization Tool

## Quick Start

1. **Start the PHP server**:
   ```bash
   php -S localhost:8000 -t public
   ```

2. **Open your browser**: Navigate to `http://localhost:8000`

3. **Default view**: The app loads with Listening - Test 1 by default

## How to Use

### Navigation

**Left Sidebar (20% width)**:
- **Skills Section**: Click on Listening, Reading, or Writing
- **Tests Section**: Shows all available tests for the selected skill
- Active selections are highlighted in green

**Main Content (80% width)**:
- Displays the selected test with all parts and questions
- Shows context information when available
- Provides input fields for answers

### Taking a Test

1. **Select a skill** from the sidebar (Listening, Reading, or Writing)
2. **Select a test number** (Test 1, Test 2, etc.)
3. **Answer the questions**:
   - **Multiple Choice**: Click the radio button for your answer (A, B, C, or D)
   - **Fill in the Blank**: Type your answer in the text box
4. **Submit**: Click the "Submit Answers" button at the bottom

### Understanding Results

After submission, you'll see:

1. **Score Summary** (at the top):
   - Shows "Score: X / Y" (e.g., "Score: 20 / 25")
   - Displayed in a green box

2. **Question Feedback**:
   - **Green box** = Your answer was CORRECT ✓
   - **Red box** = Your answer was WRONG ✗
   - For wrong answers, you'll see: **"Correct Answer: [the right answer]"** in bold

3. **Your Answers Preserved**:
   - All your selected/typed answers remain visible
   - This helps you review what you chose vs. what was correct

## Question Types

### Multiple Choice
- Displays options A, B, C, D as radio buttons
- Select one option per question
- Example:
  ```
  Question 1: Where is Mark going at the weekend?
  ○ A. to the river
  ○ B. to the mountains
  ○ C. to the forest
  ```

### Fill in the Blank
- Displays a text input box
- Type your answer directly
- Case-insensitive matching
- Example:
  ```
  Question 11: Price of flat (£... a month)
  [_________________]
  ```

## Tips for Memorization

1. **Take the test multiple times** - Repetition helps memory
2. **Review wrong answers** - The red boxes show what you need to study
3. **Use the context** - Read the context sections carefully
4. **Try all tests** - Each test has 25 questions (5 parts × 5 questions)
5. **Switch between skills** - Practice Listening, Reading, and Writing

## Data Structure

Each test typically has:
- **5 Parts** (Part 1 through Part 5)
- **5 Questions per part** (Questions 1-25 total)
- **Mix of question types** (Multiple choice and fill-in-the-blank)

## Troubleshooting

**Problem**: "Test data not found"
- **Solution**: Check that `.txt` files exist in the `data/[skill]/` directory

**Problem**: No tests showing in sidebar
- **Solution**: Verify that test files are properly formatted with `[TEST X - PART Y]` headers

**Problem**: Answers not being graded correctly
- **Solution**: Check that each question has a `Correct: answer` line in the data file

## Example Workflow

1. Start with **Listening - Test 1**
2. Answer all 25 questions
3. Submit and review your score
4. Note which questions you got wrong (red boxes)
5. Retake the same test to improve
6. Move to **Test 2** when you're confident
7. Repeat for all 5 tests
8. Switch to **Reading** or **Writing** skills

## Color Guide

- **Green** = Correct answer / Active selection
- **Red** = Wrong answer
- **Blue** = Headers and context sections
- **Dark Gray** = Sidebar background
- **Light Gray** = Question boxes (before submission)

Enjoy your exam preparation! 🎓

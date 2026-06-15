<?php
$testId = $testId ?? 1;
$allTests = $allTests ?? [];
$testData = $testData ?? null;
$results = $results ?? null;
$userAnswers = $userAnswers ?? [];
?>

<div class="sidebar">
    <div class="sidebar-header">
        <h2>Kỹ năng</h2>
    </div>
    <ul>
        <li><a href="?skill=listening"><span class="nav-icon">🎧</span> Listening</a></li>
        <li><a href="?skill=reading"><span class="nav-icon">📖</span> Reading</a></li>
        <li class="active"><a href="?skill=writing"><span class="nav-icon">✍️</span> Writing</a></li>
    </ul>

    <?php if (!empty($allTests)): ?>
    <div class="sidebar-header">
        <h2>Đề thi</h2>
    </div>
    <ul class="test-list">
        <?php foreach (array_keys($allTests) as $id): ?>
            <li class="<?php echo ($testId == $id) ? 'active' : ''; ?>">
                <a href="?skill=writing&test=<?php echo $id; ?>">Test <?php echo $id; ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</div>

<div class="main-content">
    <h1>Writing - Test <?php echo htmlspecialchars($testId); ?></h1>

    <?php if ($results): ?>
        <?php $percent = $results['total'] > 0 ? round(($results['score'] / $results['total']) * 100) : 0; ?>
        <div class="results-summary animate-fade-in">
            <div class="results-flex">
                <div class="results-text">
                    <h2>Điểm Part 1: <?php echo $results['score']; ?> / <?php echo $results['total']; ?></h2>
                    <p class="percentage"><?php echo $percent; ?>% chính xác</p>
                    <p style="color: rgba(255, 255, 255, 0.85); font-size: 13px; margin-top: 5px;">
                        * Part 2 và Part 3 cần giáo viên chấm điểm thủ công hoặc bạn tự đối chiếu đáp án mẫu.
                    </p>
                </div>
                <div class="results-actions">
                    <button 
                        type="button"
                        onclick="retryTest(this)"
                        class="btn-retry"
                    >
                        Làm lại
                    </button>
                </div>
            </div>
            <?php if ($results['total'] > 0): ?>
            <div class="progress-bar-container">
                <div class="progress-bar-fill" style="width: <?php echo $percent; ?>%"></div>
            </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($testData && !empty($testData['parts'])): ?>
        <form action="?skill=writing&test=<?php echo htmlspecialchars($testId); ?>" method="post" id="test-form">
            <?php foreach ($testData['parts'] as $part): ?>
                <div class="part-container animate-fade-in">
                    <h2>Part <?php echo $part['id']; ?></h2>
                    
                    <?php if (!empty($part['context'])): ?>
                        <?php
                        // Parse context to format bullet points
                        $context = $part['context'];
                        
                        // Check if context has "you should:" pattern
                        if (preg_match('/(.+?you should:)(.+)/is', $context, $matches)) {
                            $intro = trim($matches[1]);
                            $requirements = trim($matches[2]);
                            
                            // Split requirements by comma
                            $reqList = array_map('trim', explode(',', $requirements));
                            ?>
                            <div class="context" style="background: #f8f9fa; padding: 15px; border-left: 4px solid #2196F3; margin-bottom: 20px;">
                                <strong>Yêu cầu đề bài (Task):</strong>
                                <p style="margin: 10px 0;"><?php echo nl2br(htmlspecialchars($intro)); ?></p>
                                <ul style="margin: 10px 0 0 20px; line-height: 1.8;">
                                    <?php foreach ($reqList as $req): ?>
                                        <?php if (!empty($req)): ?>
                                            <li><?php echo htmlspecialchars($req); ?></li>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php } else { ?>
                            <p class="context"><strong>Yêu cầu đề bài (Task):</strong> <?php echo nl2br(htmlspecialchars($part['context'])); ?></p>
                        <?php } ?>
                    <?php endif; ?>

                    <?php if ($part['type'] === 'sentence_building'): ?>
                        <!-- Part 1: Sentence Building (Auto-graded) -->
                        <?php if (!empty($part['questions'])): ?>
                            <?php foreach ($part['questions'] as $question): ?>
                                <?php
                                $qId = $question['id'] ?? 0;
                                $qText = $question['text'] ?? '';
                                $resultClass = '';
                                if ($results && isset($results['details'][$qId])) {
                                    $resultClass = $results['details'][$qId]['correct'] ? 'correct-answer' : 'wrong-answer';
                                }
                                ?>
                                <div class="question-box <?php echo $resultClass; ?>">
                                    <p class="question-text">
                                        <strong>Câu hỏi <?php echo $qId; ?>:</strong> 
                                        <?php echo htmlspecialchars($qText); ?>
                                    </p>
                                    <input 
                                        type="text" 
                                        name="answers[<?php echo $qId; ?>]" 
                                        value="<?php echo htmlspecialchars($userAnswers[$qId] ?? ''); ?>" 
                                        class="text-input"
                                        placeholder="Nhập câu viết hoàn chỉnh của bạn..."
                                        style="width: 100%; padding: 10px; font-size: 14px;"
                                        <?php echo $results ? 'readonly' : ''; ?>
                                    >
                                    
                                    <?php if ($results && isset($results['details'][$qId])): ?>
                                        <?php if ($results['details'][$qId]['correct']): ?>
                                            <p style="color: #4CAF50; margin-top: 10px; font-weight: bold;">
                                                ✓ Chính xác!
                                            </p>
                                        <?php else: ?>
                                            <?php 
                                            $userAns = $results['details'][$qId]['user_answer'] ?? '';
                                            $correctAns = $results['details'][$qId]['correct_answer'] ?? '';
                                            ?>
                                            <?php if (!empty($userAns)): ?>
                                                <p class="correct-answer-text" style="color: #f44336; margin-top: 10px; border-left-color: #f44336; background-color: #ffebee;">
                                                    <strong>✗ Câu trả lời của bạn:</strong> 
                                                    <?php echo htmlspecialchars($userAns); ?>
                                                </p>
                                            <?php endif; ?>
                                            <?php if (!empty($correctAns)): ?>
                                                <p class="correct-answer-text" style="color: #4CAF50; margin-top: 5px; border-left-color: #4CAF50; background-color: #e8f5e9;">
                                                    <strong>✓ Đáp án đúng gợi ý:</strong> 
                                                    <?php echo htmlspecialchars($correctAns); ?>
                                                </p>
                                            <?php else: ?>
                                                <p style="color: #999; margin-top: 5px;">
                                                    <em>Lỗi: Không tìm thấy đáp án đúng trong cơ sở dữ liệu</em>
                                                </p>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color: #999;">Không có câu hỏi cho phần này.</p>
                        <?php endif; ?>

                    <?php else: ?>
                        <!-- Part 2 & 3: Writing Task (Manual grading) -->
                        <div class="writing-task-box">
                            <label style="display: block; margin-bottom: 10px; font-weight: bold;">
                                Bài viết của bạn:
                            </label>
                            <textarea 
                                name="writing_part_<?php echo $part['id']; ?>" 
                                rows="8" 
                                class="textarea-input"
                                placeholder="Viết bài luận của bạn tại đây..."
                            ><?php echo htmlspecialchars($_POST['writing_part_' . $part['id']] ?? $userAnswers['writing_part_' . $part['id']] ?? ''); ?></textarea>
                            
                            <?php if (!empty($part['sample_answer'])): ?>
                                <div style="margin-top: 15px;">
                                    <button 
                                        type="button" 
                                        onclick="toggleSampleAnswer(<?php echo $part['id']; ?>)"
                                        class="btn-toggle-sample"
                                    >
                                        <span id="toggle-icon-<?php echo $part['id']; ?>">👁️</span>
                                        <span id="toggle-text-<?php echo $part['id']; ?>">Xem Đáp án Mẫu</span>
                                    </button>
                                </div>
                                
                                <div 
                                    id="sample-answer-<?php echo $part['id']; ?>" 
                                    style="display: none; background: #f9f9f9; padding: 15px; border-left: 4px solid #4CAF50; margin-top: 15px; border-radius: 4px;"
                                >
                                    <strong>✅ Đáp án mẫu gợi ý (Sample Answer):</strong>
                                    <p style="margin: 10px 0 0 0; line-height: 1.6; white-space: pre-line;">
                                        <?php echo htmlspecialchars($part['sample_answer']); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <p style="color: #ff9800; font-size: 13px; margin-top: 10px;">
                                <em>⚠️ Phần này tự đối chiếu hoặc nhờ giáo viên chấm điểm. Hãy tự chấm và đối chiếu với đáp án mẫu ở trên.</em>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
            <button type="submit" class="submit-btn">Nộp bài</button>
        </form>
    <?php else: ?>
        <div class="part-container">
            <p>Không tìm thấy dữ liệu test cho Test <?php echo htmlspecialchars($testId); ?>.</p>
        </div>
    <?php endif; ?>
</div>

<script>
function toggleSampleAnswer(partId) {
    const sampleDiv = document.getElementById('sample-answer-' + partId);
    const toggleIcon = document.getElementById('toggle-icon-' + partId);
    const toggleText = document.getElementById('toggle-text-' + partId);
    
    if (sampleDiv.style.display === 'none') {
        sampleDiv.style.display = 'block';
        toggleIcon.textContent = '🙈 ';
        toggleText.textContent = 'Ẩn Đáp án Mẫu';
    } else {
        sampleDiv.style.display = 'none';
        toggleIcon.textContent = '👁️ ';
        toggleText.textContent = 'Xem Đáp án Mẫu';
    }
}
</script>

<?php
// Ensure all variables are defined
$skill = $skill ?? 'listening';
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
        <li class="<?php echo ($skill === 'listening') ? 'active' : ''; ?>"><a href="?skill=listening"><span class="nav-icon">🎧</span> Listening</a></li>
        <li class="<?php echo ($skill === 'reading') ? 'active' : ''; ?>"><a href="?skill=reading"><span class="nav-icon">📖</span> Reading</a></li>
        <li class="<?php echo ($skill === 'writing') ? 'active' : ''; ?>"><a href="?skill=writing"><span class="nav-icon">✍️</span> Writing</a></li>
    </ul>

    <?php if (!empty($allTests)): ?>
    <div class="sidebar-header">
        <h2>Đề thi</h2>
    </div>
    <ul class="test-list">
        <?php foreach (array_keys($allTests) as $id): ?>
            <li class="<?php echo ($testId == $id) ? 'active' : ''; ?>"><a href="?skill=<?php echo htmlspecialchars($skill); ?>&test=<?php echo $id; ?>">Test <?php echo $id; ?></a></li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</div>

<div class="main-content">
    <form action="?skill=<?php echo htmlspecialchars($skill); ?>&test=<?php echo htmlspecialchars($testId); ?>" method="post" id="test-form">
        <h1><?php echo htmlspecialchars(ucfirst($skill)); ?> - Test <?php echo htmlspecialchars($testId); ?></h1>

        <?php if ($results): ?>
            <?php $percent = round(($results['score'] / $results['total']) * 100); ?>
            <div class="results-summary animate-fade-in">
                <div class="results-flex">
                    <div class="results-text">
                        <h2>Kết quả: <?php echo $results['score']; ?> / <?php echo $results['total']; ?></h2>
                        <p class="percentage"><?php echo $percent; ?>% chính xác</p>
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
                <div class="progress-bar-container">
                    <div class="progress-bar-fill" style="width: <?php echo $percent; ?>%"></div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($testData && !empty($testData)): ?>
            <?php foreach ($testData as $partId => $part): ?>
                <div class="part-container animate-fade-in">
                    <h2><?php echo htmlspecialchars($part['title'] ?? 'Part ' . $partId); ?></h2>
                    
                    <?php if (!empty($part['audio'])): ?>
                        <div class="audio-player">
                            <div class="audio-player-header">
                                <span>🎧</span>
                                <h3>Audio cho Part này</h3>
                            </div>

                            <?php
                            // Extract file ID from Google Drive URL or use local filename
                            $audioUrl = $part['audio'];
                            
                            // Check if it's a Google Drive URL
                            if (strpos($audioUrl, 'drive.google.com') !== false) {
                                $fileId = null;
                                if (preg_match('/\/d\/([^\/]+)/', $audioUrl, $matches)) {
                                    $fileId = $matches[1];
                                } elseif (preg_match('/[?&]id=([^&]+)/', $audioUrl, $matches)) {
                                    $fileId = $matches[1];
                                }
                                
                                if ($fileId) {
                                    // Add #t=0 to prevent autoplay
                                    $embedUrl = "https://drive.google.com/file/d/{$fileId}/preview#t=0";
                                } else {
                                    $embedUrl = $audioUrl;
                                }
                            } else {
                                // It's a local filename - construct path with TEST folder
                                $embedUrl = "audio/TEST%20" . $testId . "/" . htmlspecialchars($audioUrl);
                            }
                            ?>

                            <div class="audio-player-container">
                                <div class="audio-controls">
                                    <button type="button" class="audio-btn" onclick="rewindAudio(event)">⏮ -5s</button>
                                    <audio controls class="audio-element">
                                        <source src="<?php echo htmlspecialchars($embedUrl); ?>" type="audio/mpeg">
                                    </audio>
                                    <button type="button" class="audio-btn" onclick="forwardAudio(event)">+5s ⏭</button>
                                    
                                    <div class="speed-control">
                                        <label for="speed-select-<?php echo $partId; ?>">Tốc độ:</label>
                                        <select id="speed-select-<?php echo $partId; ?>" class="speed-select" onchange="changePlaybackSpeed(event)">
                                            <option value="0.75">0.75x</option>
                                            <option value="1.0" selected>1.0x</option>
                                            <option value="1.25">1.25x</option>
                                            <option value="1.5">1.5x</option>
                                            <option value="1.75">1.75x</option>
                                            <option value="2.0">2.0x</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <p class="audio-player-footer">
                                💡 <a href="<?php echo htmlspecialchars($embedUrl); ?>" target="_blank">Mở audio</a> nếu không nghe được
                            </p>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($part['context'])): ?>
                        <?php 
                        $context = $part['context'];
                        // Check if context has column layout
                        if (strpos($context, '[LEFT COLUMN]') !== false && strpos($context, '[RIGHT COLUMN]') !== false) {
                            // Split into left and right columns
                            $parts = explode('[RIGHT COLUMN]', $context);
                            $leftPart = str_replace('[LEFT COLUMN]', '', $parts[0]);
                            $rightPart = $parts[1];
                            
                            // Clean up and format
                            $leftPart = str_replace(' --- ', "\n", $leftPart);
                            $rightPart = str_replace(' --- ', "\n", $rightPart);
                        ?>
                            <div class="context context-two-columns">
                                <div class="context-column context-left">
                                    <?php echo nl2br(htmlspecialchars(trim($leftPart))); ?>
                                </div>
                                <div class="context-column context-right">
                                    <?php echo nl2br(htmlspecialchars(trim($rightPart))); ?>
                                </div>
                            </div>
                        <?php } else { ?>
                            <p class="context"><strong>Tình huống / Đoạn văn:</strong> <?php echo nl2br(htmlspecialchars(str_replace(' --- ', "\n", $part['context']))); ?></p>
                        <?php } ?>
                    <?php endif; ?>

                    <?php if (!empty($part['questions'])): ?>
                        <?php foreach ($part['questions'] as $qId => $question): ?>
                            <?php
                            $resultClass = '';
                            if ($results && isset($results['details'][$qId])) {
                                $resultClass = $results['details'][$qId]['correct'] ? 'correct-answer' : 'wrong-answer';
                            }
                            ?>
                            <div class="question-box <?php echo $resultClass; ?>">
                                <p class="question-text"><strong>Câu hỏi <?php echo $qId; ?>:</strong> <?php echo htmlspecialchars($question['text'] ?? ''); ?></p>
                                
                                <?php if ($part['type'] === 'multiple_choice' && !empty($question['options'])): ?>
                                    <div class="options">
                                        <?php foreach ($question['options'] as $optKey => $optVal): ?>
                                            <?php
                                            $checked = '';
                                            $activeClass = '';
                                            if (isset($userAnswers[$qId]) && $userAnswers[$qId] === $optKey) {
                                                $checked = 'checked';
                                                $activeClass = 'selected-option';
                                            }
                                            ?>
                                            <label class="<?php echo $activeClass; ?>">
                                                <input type="radio" name="answers[<?php echo $qId; ?>]" value="<?php echo htmlspecialchars($optKey); ?>" <?php echo $checked; ?> onchange="highlightOption(this)">
                                                <span class="option-letter"><?php echo htmlspecialchars($optKey); ?></span>
                                                <span class="option-text"><?php echo htmlspecialchars($optVal); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: // Fill in the blank / Matching ?>
                                    <div class="answer-input-wrapper">
                                        <input type="text" name="answers[<?php echo $qId; ?>]" value="<?php echo htmlspecialchars($userAnswers[$qId] ?? ''); ?>" class="text-input" placeholder="Nhập câu trả lời của bạn..." <?php echo $results ? 'readonly' : ''; ?>>
                                    </div>
                                <?php endif; ?>

                                <?php if ($results && isset($results['details'][$qId]) && !$results['details'][$qId]['correct']): ?>
                                    <div class="correct-answer-text">
                                        <strong>Đáp án đúng:</strong> <?php echo htmlspecialchars($results['details'][$qId]['correct_answer']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <button type="submit" class="submit-btn">Nộp bài</button>
        <?php else: ?>
            <div class="part-container">
                <p>Không tìm thấy dữ liệu test cho kỹ năng và số test đã chọn.</p>
                <p>Vui lòng kiểm tra:</p>
                <ul>
                    <li>Thư mục <code>data/<?php echo htmlspecialchars($skill); ?>/</code> có tồn tại không</li>
                    <li>Có file .txt nào trong thư mục không</li>
                    <li>File có đúng định dạng không</li>
                </ul>
            </div>
        <?php endif; ?>
    </form>
</div>

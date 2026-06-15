<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Công cụ ôn tập và ghi nhớ đề thi Tiếng Anh Listening, Reading, Writing">
    <title>Chào mừng - English Test Practice</title>
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: radial-gradient(circle at top right, #1e293b 0%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #f1f5f9;
        }
        
        .welcome-container {
            background: rgba(30, 41, 59, 0.45);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            padding: 60px 40px;
            text-align: center;
            max-width: 550px;
            width: 100%;
            animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo-icon {
            font-size: 3.5em;
            margin-bottom: 20px;
            display: inline-block;
            filter: drop-shadow(0 4px 10px rgba(59, 130, 246, 0.3));
            animation: float 4s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }
        
        h1 {
            color: #ffffff;
            font-size: 2.6em;
            margin-bottom: 15px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        
        .subtitle {
            color: #94a3b8;
            font-size: 1.15em;
            margin-bottom: 45px;
            line-height: 1.6;
        }
        
        .start-button {
            display: inline-block;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 16px 48px;
            font-size: 1.2em;
            font-weight: 600;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        }
        
        .start-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(59, 130, 246, 0.6);
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        }
        
        .start-button:active {
            transform: translateY(-1px);
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 50px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding-top: 30px;
        }
        
        .feature-item {
            text-align: center;
        }
        
        .feature-icon {
            font-size: 1.5em;
            margin-bottom: 8px;
            display: block;
        }
        
        .feature-label {
            font-size: 0.85em;
            color: #94a3b8;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .welcome-container {
                padding: 40px 25px;
            }
            
            h1 {
                font-size: 2em;
            }
            
            .subtitle {
                font-size: 1em;
                margin-bottom: 35px;
            }
            
            .start-button {
                padding: 14px 40px;
                font-size: 1.1em;
            }
            
            .features-grid {
                margin-top: 40px;
                padding-top: 25px;
            }
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <span class="logo-icon">🎓</span>
        <h1>English Practice</h1>
        <p class="subtitle">
            Hệ thống ôn luyện đề thi và ghi nhớ đáp án thông minh<br>cho Listening, Reading và Writing.
        </p>
        
        <a href="?skill=listening&test=1" class="start-button">
            Bắt đầu luyện tập
        </a>
        
        <div class="features-grid">
            <div class="feature-item">
                <span class="feature-icon">🎧</span>
                <span class="feature-label">Listening</span>
            </div>
            <div class="feature-item">
                <span class="feature-icon">📖</span>
                <span class="feature-label">Reading</span>
            </div>
            <div class="feature-item">
                <span class="feature-icon">✍️</span>
                <span class="feature-label">Writing</span>
            </div>
        </div>
    </div>
</body>
</html>

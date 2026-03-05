<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: #f5f5f5;
            padding: 20px;
        }

        h1 {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 24px;
        }

        button {
            padding: 12px 24px;
            font-size: 1rem;
            color: #fff;
            background: #333;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.2s;
        }

        button:hover {
            background: #555;
        }
    </style>
</head>
<body>
    <h1>Not Found</h1>
    <button onclick="history.back()">Back</button>
</body>
</html>
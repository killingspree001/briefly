# Briefly.ai ⚡

Briefly.ai is a fully automated, AI-powered news intelligence hub. It fetches global headlines, processes them via a custom extractive summarization engine, and delivers a sleek "Daily Briefing" that fits in your pocket.

![Briefly.ai Preview](https://via.placeholder.com/800x450.png?text=Briefly.ai+Preview)

## ✨ Features

- **Daily Briefing**: A curated view of today's top stories in Tech, Finance, and Science.
- **AI Summarization**: Custom Python-based extractive summarization and sentiment analysis.
- **Archive System**: Access the past 7 days of news, neatly grouped by date.
- **Fully Automated**: Hourly fetch/process cycles (via Vercel Cron Jobs).
- **Mobile Optimized**: Premium glassmorphism UI that feels like a native app.
- **PDF Export**: Download today's briefing for offline reading.
- **Privacy First**: No tracking, no bloat, just intelligence.

## 🛠️ Tech Stack

- **Frontend**: Vanilla CSS (Modern Grid/Flex), JavaScript (ES6+).
- **Backend API**: PHP 8.x (Serverless Functions).
- **AI Microservice**: Python 3.9 (Extractive Summarization, TextBlob).
- **Database**: MySQL (TiDB Serverless recommended).
- **Deployment**: Vercel.

## 🚀 Local Development

1. **Clone the repository**:
   ```bash
   git clone https://github.com/killingspree001/briefly.git
   cd briefly
   ```

2. **Environment Setup**:
   Copy `.env` and fill in your credentials:
   ```bash
   # .env
   DB_HOST=localhost
   DB_NAME=briefly_ai
   DB_USER=root
   DB_PASS=
   NEWS_API_KEY=your_key_here
   SUMMARIZER_URL=http://localhost:8000/api/summarize
   ```

3. **Install Dependencies**:
   ```bash
   pip install -r requirements.txt
   ```

4. **Run Local Server**:
   ```bash
   php -S localhost:8000 router.php
   ```

## ☁️ Deployment (Vercel)

This project is optimized for **Vercel Serverless Functions**.

1.  Push your code to GitHub.
2.  Connect your repository to Vercel.
3.  Add the environment variables in the Vercel Dashboard.
4.  The `vercel.json` will automatically handle routing and hourly cron jobs.

## 📝 License

MIT © [killingspree001](https://github.com/killingspree001)

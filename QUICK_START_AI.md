# 🚀 QUICK START - AI Feature

## Setup (2 minutes)

### 1. Get API Key
```bash
Go to: https://console.groq.com
Sign up (free) → API Keys → Create → Copy key
```

### 2. Add to .env
```env
GROQ_API_KEY=gsk_your_key_here
```

### 3. Test It!
```bash
# Navigate to any patient in your app
http://127.0.0.1:8000/patients/1

# Click purple "AI Summary" button
# Wait 2-3 seconds
# See magic! ✨
```

---

## That's It! 🎉

The feature is fully implemented and ready to use.

---

## Files Changed

✅ **Backend:**
- `app/Services/AIService.php` (NEW)
- `app/Http/Controllers/PatientController.php`
- `routes/web.php`

✅ **Frontend:**
- `resources/views/patients/show.blade.php`
- `resources/views/layouts/app.blade.php`

✅ **Config:**
- `.env.example`

---

## Documentation

📚 **Full Setup Guide:** `AI_SETUP_GUIDE.md`
🎨 **Visual Guide:** `AI_VISUAL_GUIDE.md`

---

## Need Help?

Common issues:

**"GROQ_API_KEY not configured"**
→ Add key to `.env` file

**"Network error"**
→ Check internet connection

**Summary is generic**
→ Patient needs more medical history data

---

**Questions?** Check `AI_SETUP_GUIDE.md` for details!

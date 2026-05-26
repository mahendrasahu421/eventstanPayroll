# TODO - Integrate Gemini AI into Payroll App

- [ ] Add GEMINI_API_KEY and GEMINI_MODEL to .env (and .env.example if present)
- [ ] Create App\Services\GeminiService.php to call Google Gemini API securely (no hardcoded key)
- [ ] Add AI controller: App\Http\Controllers\AI\PayrollAIController.php with endpoint to explain a salary slip
- [ ] Register routes in routes/web.php under authenticated payroll prefix
- [ ] Update resources/views/payroll/salary-slip.blade.php to add UI (textarea + button) and AJAX call to AI endpoint
- [ ] Add basic logging/error handling and response rendering in UI
- [x] Clear config/cache and manually test salary slip AI explanation feature



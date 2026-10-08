# Silent Scrapers

**Discover. Analyze. Secure.**

[العربية](./README.ar.md)

An educational cybersecurity platform that combines a modern web UI, a reconnaissance engine, and hands-on labs. It is built for students, authorized testers, and researchers who want to learn how digital-asset discovery works in a legal, controlled setting.

> **Ethical use only.** Use these tools on systems you own, on engagements with explicit written permission, or in isolated training environments. Do not target unauthorized systems.

---

## Overview

Silent Scrapers brings together three pieces that are usually separate:

1. **A web frontend** that introduces the project, the team, and learning paths.
2. **A reconnaissance backend** that wraps well-known tools (Subfinder, Assetfinder, Katana, Naabu, Gau, Sublist3r, Nmap) behind a single API.
3. **Labs and practice content** covering web and network security concepts, plus CTF-style challenges, learning paths, and curated resources.

The goal is to walk through a reconnaissance workflow (subdomain discovery, port checks, ASN-to-network mapping) from one UI instead of running every tool from the terminal.

---

## Features

### Reconnaissance (Search page)

| Mode | What it does |
| --- | --- |
| **Web** | Subdomain discovery, crawling, URLs, and host checks through multiple tools |
| **All in One** | Runs the tool set together against the same domain |
| **3 SUBS** | Subfinder + Assetfinder + Sublist3r in one pass |
| **Network** | Looks up ASN prefixes, then runs a limited Nmap port check |

The UI checks whether Go and the recon tools are installed, groups results (reachable / unreachable), and stores the last scan in the browser.

### Educational labs

**Web**

- Zero Team — weak authentication, file upload, privilege escalation  
- Hacker News Portal — SQL injection, XSS, command injection  
- Noon Store — SQL injection, XSS, weak authentication  
- Ticket Reservation System — SQL injection, XSS, session management  

Labs 1–3 open on a local server (`http://localhost/laps/...`). Lab 4 is embedded in the app at `/web-labs/Lab4`.

**Network**

- ARP Spoofing — concept pages plus a lab in a controlled training environment

### Learning content

- **Learning Paths** — from fundamentals to labs, then CTF, then advanced resources  
- **Challenges** — short web / network / crypto exercises with optional solutions  
- **Resources** — courses, tools, books, and communities  
- **FAQ** — common questions about safe lab use  
- **About** — project mission and ethical-use statement  
- **Home** — platform intro and team cards  

---

## Tech stack

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

| Layer | Technologies |
| --- | --- |
| Frontend | React 18, TypeScript, Vite 5, React Router 6 |
| UI / motion | Tailwind CSS, Framer Motion, Lucide / React Icons |
| Backend | Python 3.8+, FastAPI, Uvicorn, Pydantic, Requests |
| Recon | Subfinder, Assetfinder, Katana, Naabu, Gau, Sublist3r |
| Network | Nmap, BGPView (ASN) |
| Labs 1–3 | Local PHP apps (`localhost/laps`) |

---

## Project structure

```
Silent Scrapers/
├── README.md                          ← this file (English)
├── README.ar.md                       ← Arabic version
├── Source Code/project/
│   ├── requirements.txt               ← Python libraries
│   └── project/
│       ├── run_nmap/                  ← helper Nmap / ASN modules
│       └── project/                   ← the actual app
│           ├── app.py                 ← FastAPI backend
│           ├── package.json           ← Vite + React frontend
│           ├── src/                   ← React pages and components
│           └── Sublist3r/             ← subdomain enumeration tool
└── screen shot/                       ← platform screenshots (if present)
```

Main frontend routes:

| Path | Page |
| --- | --- |
| `/` | Home |
| `/about` | About |
| `/search` | Recon tools |
| `/labs` | Labs |
| `/learning-paths` | Learning paths |
| `/challenges` | CTF challenges |
| `/resources` | Resources |
| `/faq` | FAQ |

---

## Prerequisites

Install these before you run the project:

| Tool | Suggested version | Purpose |
| --- | --- | --- |
| [Python](https://www.python.org/downloads/) | 3.8+ | Backend and Sublist3r |
| [Node.js](https://nodejs.org/) | 18+ (includes npm) | Frontend |
| [Nmap](https://nmap.org/download.html) | Recent release | Port scanning |
| [Go](https://go.dev/dl/) | 1.20+ | Installing recon tools |
| Git | Optional | Cloning the repo |

On Windows, add Nmap and Go to **PATH**. The backend looks for Nmap at:

`C:\Program Files (x86)\Nmap\nmap.exe`

Labs 1–3 need a local PHP server (for example XAMPP) with files under `http://localhost/laps/`. The UI and Search tools work without that.

---

## Setup and run

Run these commands from the repository root after cloning.

### 1) Backend (FastAPI)

```powershell
cd "Source Code/project"

python -m venv .venv
.\.venv\Scripts\Activate.ps1

pip install fastapi==0.104.1 uvicorn==0.24.0 pydantic==2.4.2 requests==2.31.0 beautifulsoup4==4.12.2 lxml==4.9.3 python-nmap==0.7.1
```

Start the server from the folder that contains `app.py`:

```powershell
cd "Source Code/project/project/project"
uvicorn app:app --reload --host 127.0.0.1 --port 8000
```

Interactive API docs should be available at [http://127.0.0.1:8000/docs](http://127.0.0.1:8000/docs).

On Linux or macOS, create the venv the same way, then activate with `source .venv/bin/activate`.

### 2) Frontend (React + Vite)

In a second terminal:

```powershell
cd "Source Code/project/project/project"
npm install
npm run dev
```

Open the URL Vite prints (usually [http://localhost:5173](http://localhost:5173)).

The UI talks to the backend at `http://localhost:8000`. Start the backend first, or the Search page will report that the API is unavailable.

### 3) Recon tools (Go)

From the **Search** page you can install Go and the tools through the UI (Windows), or install them yourself:

```powershell
go install github.com/projectdiscovery/subfinder/v2/cmd/subfinder@latest
go install github.com/tomnomnom/assetfinder@latest
go install github.com/projectdiscovery/katana/cmd/katana@latest
go install github.com/projectdiscovery/naabu/v2/cmd/naabu@latest
go install github.com/lc/gau/v2/cmd/gau@latest
```

Make sure `%USERPROFILE%\go\bin` (Windows) or `$HOME/go/bin` (Linux/macOS) is on PATH.

Sublist3r extras:

```powershell
pip install argparse dnspython requests
```

---

## Using the platform

1. Open the UI and read Home and **About**.
2. Go to **Search** and confirm Go and the tools show as installed.
3. Open the **Web** tab, enter a domain you own or are authorized to test, then pick a tool (for example `3 SUBS` or `All in One`).
4. For network mapping, use the **Network** tab and an ASN when needed to fetch prefixes and run a limited port check.
5. From **Labs**, open a lab that matches your level. Labs 1–3 need local PHP.
6. Follow **Learning Paths**, **Challenges**, and **Resources** in whatever order fits your study plan.

---

## API

The backend listens on port **8000**.

| Method | Path | Purpose |
| --- | --- | --- |
| GET | `/api/three-subs?domain=` | Three subdomain sources |
| GET | `/api/katana?domain=` | Crawl / URLs |
| GET | `/api/naabu?domain=` | Open ports on a host |
| GET | `/api/gau?domain=` | Historical URLs |
| GET | `/api/all_in_one?domain=` | Run tools together (optional ASN) |
| GET | `/api/check_domain?domain=` | HTTP/HTTPS reachability |
| GET | `/api/check_go` | Whether Go is installed |
| GET | `/api/check_tools` | Go tool status |
| GET | `/api/install_go` | Install Go (Windows) |
| GET | `/api/install_tools` | Install Go tools (Windows) |
| GET | `/api/uninstall` | Remove Go/tools (Windows) |
| POST | `/api/asn_lookup` | Network prefixes for an ASN |
| POST | `/api/nmap_scan` | Limited port scan for hosts/CIDRs |

Quick check after starting the backend:

```powershell
curl "http://127.0.0.1:8000/api/check_go"
```

---

## Production build (frontend)

```powershell
cd "Source Code/project/project/project"
npm run build
npm run preview
```

Output goes to `dist`. The backend remains a separate Python process on port 8000.

---

## Troubleshooting

| Problem | What to do |
| --- | --- |
| UI says the API is unavailable | Start `uvicorn` on port 8000 before opening Search |
| `nmap not found` or empty results | Install Nmap, add it to PATH, or confirm the default Windows install path |
| Go tools missing | Run `go install ...` or use the Search install buttons, then open a new terminal |
| `pip` fails because of React packages in `requirements.txt` | Install only the Python packages listed in the backend step; frontend packages come from `npm install` |
| Labs 1–3 do not open | Serve PHP files under `http://localhost/laps/` (for example with XAMPP) |
| Port 8000 or 5173 already in use | Stop the process using it, or change the port in the start command |

---

## Important limits

- Recon and scanning are allowed **only** on systems you own or have written authorization to test.
- Installing and uninstalling Go/tools from the UI is built for **Windows**.
- Backend Nmap use is limited (ports 1–1000 and a small sample of addresses per CIDR) so a local run does not become a wide scan.
- This is an educational project. It does not provide full lab isolation, user accounts, or a production database.

---

## Team

| Name | Role |
| --- | --- |
| Karim Mohamed | Leader |
| Mohamed Hesham | Penetration Tester (Sub Leader) |
| Ebrahim Hesham | Penetration Tester (Sub Leader) |
| mohamed-ebrahim | Penetration Tester |
| yossif-ayman | Penetration Tester |
| mohamed-elsayed | SOC Analyst |
| kerolos-adeb | Frontend Lead |
| karim-tark | Backend Lead |
| mostafa-ahmed | Team Member |
| mohamed-ahmed | Team Member |
| Mostafa Mohie | Team Member |
---

## Contributing

The current tree is meant for local development and educational demos. If you suggest a change:

1. Describe the issue or idea clearly.
2. Include OS plus Python and Node versions.
3. Do not attach real scan targets or sensitive data.

---

## License and usage

There is no separate license file in the repository. The content is provided for educational use. You are responsible for complying with the law and the policies of any organization whose systems you test.

Third-party tools (Nmap, ProjectDiscovery, Sublist3r, and others) remain under their own licenses.

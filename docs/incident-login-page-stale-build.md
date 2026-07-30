# Incident: Production Login Page Shows Unstyled / Old UI

**Date reported:** 2026-07-30
**Reported by:** Rachman (dev, local Windows laptop)
**Affected environment:** Production — `http://103.55.38.96`
**Severity:** Cosmetic / UX (login still functions, but looks broken — unstyled, full-width, no branding layout)
**Status:** Root cause identified, fix not yet deployed

---

## 1. Symptom

On the development laptop, `/login` renders the redesigned Ant Design login card: centered, max-width 420px, rounded card with shadow, gradient background, branding block (title + subtitle) inside the card.

On production (`http://103.55.38.96/login`), the page renders with:

- No centering — fields stretch full viewport width, edge-to-edge
- No card boundary / shadow / rounded corners
- No gradient background, large blank black area above the title
- Basically looks like a raw, unstyled HTML form

Screenshot reference: `docs/login-error.txt` (raw HTML response captured from production) plus a browser screenshot showing the broken layout.

## 2. Root Cause

**Production is serving a stale frontend build — compiled from an older commit, before the Ant Design login redesign. This is a deployment sync issue, not a code bug.**

### Evidence

**a) Hashed asset filenames don't match the current source**

The HTML captured from production (`docs/login-error.txt`, line 34) requests:

```
app-C6G_3qQV.css
app-BSDKbX4x.js
```

...and its module-prefetch list (same file) includes chunks such as:

```
Login-6WeG4Su5.js
GuestLayout-CdHxrcIx.js
PrimaryButton-CGvhAk2J.js
TextInput-Rq16YUI0.js
InputLabel-Cc78vgCs.js
ApplicationLogo-DXl0ZJzz.js
```

`PrimaryButton`, `TextInput`, `InputLabel`, and `ApplicationLogo` are **leftover Laravel Breeze components**. The current `resources/js/Pages/Auth/Login.tsx` and `resources/js/Layouts/GuestLayout.tsx` no longer import any of them — they were fully replaced by Ant Design components (`Card`, `Form`, `Input`, `Button`, etc.) and inline-style layout.

Building locally today (`npm run build`) produces a **different hash** for the same page:

```
"resources/js/Pages/Auth/Login.tsx": { "file": "assets/Login-CBmUCmok.js", ... }
```

This confirms production's `public/build/manifest.json` (and the compiled JS/CSS it points to) predates the redesign.

**b) Git history confirms when the redesign landed**

```
44bb0ec fix: centered login card — max-width 420px, branding inside form, no full-width stretch
9fb8e21 fix: responsive login page — centered card, max-width constraint, mobile padding
```

Both commits are recent (Jul 29 2026). Production has clearly not been rebuilt/redeployed since.

**c) Why the old bundle looks this broken**

The *old* Breeze-style `GuestLayout` relied on **Tailwind utility classes** (`min-h-screen flex flex-col items-center bg-gray-100 ...`) for centering, padding, and card styling. The *current* `GuestLayout.tsx` instead uses **inline styles** (flexbox centering, `maxWidth: 420`, gradient background) so it degrades gracefully even if some CSS fails to load.

Production is running the old JS (which needs Tailwind classes to look right) alongside a CSS bundle that no longer matches it correctly — producing exactly the symptom seen: no centering, no max-width, no card visuals, stretched full-width inputs.

## 3. Why This Happened (deployment gap)

- `Dockerfile` correctly builds the frontend fresh on every image build:

```dockerfile
FROM node:22-alpine AS frontend
...
RUN npm run build
...
COPY --from=frontend /app/public/build ./public/build
```

  This part of the pipeline is **not the problem** — a fresh image build always compiles current source.

- `.github/workflows/ci.yml` only runs **tests** (Pint, PHPUnit, `npm run build` for build-verification) on push/PR. **There is no CD/auto-deploy step** that rebuilds and redeploys the Docker image to the production host after merge.

- `docker-compose.yml` (production) builds the `app` service from the local `Dockerfile` context (`build: .`) and runs on port `3005`, presumably behind a reverse proxy that exposes it as `http://103.55.38.96`.

**Conclusion:** Someone needs to manually (or via a CD pipeline that doesn't exist yet) rebuild the Docker image from latest `main`/deploy branch and redeploy the container. That step was missed after commits `9fb8e21` and `44bb0ec`.

## 4. Recommended Fix (steps for the deployment agent)

1. **Pull latest code** on the production host (must include commits `9fb8e21` and `44bb0ec`, and anything after them):
   ```bash
   cd /path/to/dpr-app
   git fetch origin
   git checkout <deploy-branch>
   git pull
   ```

2. **Rebuild the Docker image** (this reruns `npm run build` fresh inside the `frontend` build stage):
   ```bash
   docker compose build app
   ```

3. **Recreate the container** with the new image:
   ```bash
   docker compose up -d app
   ```

4. **Clear/rebuild Laravel caches** inside the container (config/route/view cache may reference old paths):
   ```bash
   docker compose exec app php artisan config:clear
   docker compose exec app php artisan route:clear
   docker compose exec app php artisan view:clear
   docker compose exec app php artisan config:cache
   ```

5. **Verify the deployed manifest** matches the latest build:
   ```bash
   docker compose exec app cat public/build/manifest.json | grep Login
   ```
   Confirm the `Login.tsx` entry no longer points to `Login-6WeG4Su5.js` (or whatever hash was live before), and that no chunk named `PrimaryButton-*`, `TextInput-*`, `InputLabel-*`, or `ApplicationLogo-*` appears anywhere in the manifest for the `Auth/Login` page.

6. **Hard-refresh / clear service worker** when testing in browser — this app is a PWA (Vite PWA plugin, Workbox). The browser may have a previously registered service worker precaching the old JS/CSS shell. To verify the fix:
   - Open DevTools → Application → Service Workers → Unregister, then hard reload (Ctrl+Shift+R), **or**
   - Test in a private/incognito window first to rule out client-side caching.

## 5. Prevention (suggested follow-up, not required to close this incident)

- Add a **CD step** to `.github/workflows/ci.yml` (or a separate deploy workflow) that, after tests pass on the deploy branch, SSHes into the production host and runs steps 1–4 above automatically. This removes the manual "did someone remember to redeploy" gap that caused this incident.
- Consider tagging Docker images with the git commit SHA (e.g., `mineops-app:<sha>`) instead of `latest`, so it's always possible to confirm which commit is actually running in production (`docker inspect` / a `/version` health endpoint that prints the built commit SHA would help future debugging).
- Optionally expose the current build hash via a small `/api/health` or footer text (e.g., git short SHA) so this class of "old build" issue is visually obvious without needing to grep asset hashes.

# Brenora

Brenora is a Laravel 12 prototype for customer and revenue intelligence, with MySQL persistence for a XAMPP development environment.

## Local setup with XAMPP

1. Start Apache and MySQL in the XAMPP Control Panel.
2. Create a MySQL database named `brenora` (or update the connection values in `.env`).
3. Install PHP dependencies with `composer install`.
4. Copy `.env.example` to `.env` if needed, then set `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=brenora`, and your local MySQL username/password.
5. Run `php artisan key:generate`, `php artisan migrate`, and `php artisan db:seed --class=DemoDataSeeder`.
6. Install frontend dependencies and compile: `npm install` then `npm run build`.
7. Open `http://localhost/brenora.net/public` under XAMPP, or run `php artisan serve` and visit the URL it prints.

The demo seeder creates `demo@brenora.com` with password `Demo@1234`, assigns it the admin role, and adds synthetic account, signal, insight, and action records. This shared credential is for local development only: disable or replace it before exposing the app to a network or production deployment.

## Current application features

- Public home, product, about, and pricing pages with responsive square-edged layouts, parallax motion, Lottie workflow animation, plan comparison, FAQ, contact forms, a contact popup, and scroll-to-top control, plus `/sitemap.xml` and `/robots.txt`.
- Public Deal Health & Evidence Auditor at `/demo/deal-health`: a no-storage Laravel rules-based playground that highlights matched note excerpts, demo health scoring, and editable follow-up drafts. The page explicitly describes NVIDIA NIM and NeMo as proposed architecture, not active integrations; use fictional notes only.
- New registrations receive a 14-day product trial. Launch is USD $99.99 monthly / $999.90 annually; Growth is USD $189.99 monthly / $1,899.90 annually (two months free); Enterprise is quote-based. PayPal checkout grants access for the purchased period and renews manually. Trial expiration is enforced on dashboard, account, and admin routes unless paid access is active.
- Contact form submissions are validated and stored in `contact_submissions`.
- Session authentication and self-registration; newly registered users always receive the `viewer` role.
- Roles: `admin`, `analyst`, `contributor`, and `viewer`. Admins can manage roles and commercial actions; analysts can manage account data and review insights; viewers have read-only access.
- Split-column, animated login and registration pages and a responsive workspace shell with sidebar navigation, account health progress bars, at-risk alerts, and dismissible status notifications.
- Settings let each user choose which dashboard sections to display. Admins and analysts can define required or optional account custom fields; field values are validated, saved with accounts, and removed when a field is deleted.
- MySQL-backed account and signal CRUD, evidence-associated insight cards, and action status history.
- Revenue and account workflows for contacts, subscription context, consent, renewal dates, forecast history, playbook actions, owner assignments, action outcomes, and account activity.
- Account search spans contacts, signal sources and summaries, and commercial actions, with filters for health ranges, signal type, and playbooks. Administrators have a searchable workspace-wide activity log with actor, account, event, and recorded details.
- Optional Salesforce OAuth integration: administrators can initiate a manually controlled, read-only Account and Contact import. Imported records remain marked for review; unmeasured health, revenue, renewal, and pipeline values remain blank, and contact consent starts pending. The connector is inactive until configured and authorized.
- Insight generation can use a local Ollama endpoint and an open-weight model. Set `OLLAMA_BASE_URL` and `OLLAMA_MODEL` in `.env`, start Ollama, and ensure the model is pulled locally before using “Generate with local model”. The service validates response shape and evidence IDs before saving a review-required draft. Only account context and selected signal summaries are sent to the configured endpoint.
- For the default configuration, install Ollama and run `ollama pull qwen2.5:3b`. Confirm the selected checkpoint’s license and model/runtime compatibility before production use.
- The separate NVIDIA NeMo Guardrails integration remains planned, not enabled.
- NVIDIA NeMo Guardrails, NIM, RAPIDS, and Triton are conceptual technology options only; none is currently deployed.

The current demo is a single shared workspace. Multi-tenant workspace isolation and production authorization review are prerequisites before using real customer data.

### Optional Salesforce setup

1. Create a Salesforce Connected App for the production authorization host (`https://login.salesforce.com`) and enable OAuth scopes for API access and refresh tokens.
2. Register the exact callback URL for the deployment: `/admin/integrations/salesforce/callback`.
3. Set `SALESFORCE_CLIENT_ID`, `SALESFORCE_CLIENT_SECRET`, and `SALESFORCE_REDIRECT_URI` in the server environment. The redirect URI must exactly match the Connected App entry; keep the client secret out of source control.
4. Optionally set `SALESFORCE_LOGIN_URL` to `https://login.salesforce.com` or a trusted Salesforce organization domain and choose an API version.
5. Apply migrations, sign in as an administrator, open **Integrations**, and authorize the connection. Syncs are manual and import standard Account and Contact fields into this shared workspace.

Salesforce OAuth credentials are encrypted in the application database. Disconnect removes stored credentials but retains imported records. The current single-connection design is not tenant-isolated.

### Optional PayPal checkout

1. Create a PayPal REST application and set `PAYPAL_CLIENT_ID`, `PAYPAL_SECRET`, and `PAYPAL_MODE=test` in the server environment for testing.
2. Use `PAYPAL_MODE=live` with production credentials when ready to accept live payments. Keep the secret server-side and out of source control.
3. Apply migrations so checkout records and paid access expiry can be stored. Online purchase requires a signed-in workspace user; visitors can create a workspace and begin the 14-day trial first.

The application creates and captures orders server-side, verifies the selected plan amount and currency before granting access, and stores the provider order/capture references. Billing is a one-time payment for the selected monthly or annual period; renewals are manual.

## Checks

- `php artisan test`
- `npm run build`

# UNOPIM GO - Intelligent Orchestration Engine

> **Version:** 1.0 | **Project:** UnoPim PIM
> **Purpose:** Transform any task into structured, multi-phase execution with intelligent skill/agent orchestration for the UnoPim modular monolith

---

## ACTIVATION

When user invokes `/go $ARGUMENTS`, execute the orchestration protocol below.

---

## PHASE 0: CONTEXT HARVESTING (Silent - No Output)

### 0.1 Load Project Intelligence
```yaml
memory:
  - MEMORY.md                           # Project identity, key paths, architecture patterns
  - docs/index.md                       # Master documentation index
  - docs/architecture.md                # 6-layer system architecture
  - docs/data-models.md                 # 30+ database tables, JSON structures
  - docs/api-contracts.md               # 40+ REST API endpoints
  - docs/component-inventory.md         # 95+ UI components

pattern_docs:
  - docs/patterns-data-external.md      # Database grammars, Eloquent, repos, external services
  - docs/patterns-infrastructure.md     # Concord modules, ServiceProviders, DataGrid, events
  - docs/patterns-domain.md             # Product/Attribute/Category/User/DataTransfer/MagicAI
  - docs/patterns-application.md        # Controllers, routes, ACL, menus, Form Requests
  - docs/patterns-middleware.md         # HTTP Kernel, auth guards, SecureHeaders, locale
  - docs/patterns-client-designsystem.md # Vue.js 3, Blade, Tailwind, icons, dark mode

project_skills:
  - .claude/commands/unopim-patterns.md # Master reference (all layers)
  - .claude/commands/unopim-data.md     # DATA/EXTERNAL layer
  - .claude/commands/unopim-infra.md    # INFRASTRUCTURE layer
  - .claude/commands/unopim-domain.md   # DOMAIN layer
  - .claude/commands/unopim-app.md      # APPLICATION layer
  - .claude/commands/unopim-middleware.md # MIDDLEWARE layer
  - .claude/commands/unopim-client.md   # CLIENT/Design System layer
```

### 0.2 Classify Task Complexity
Silently analyze `$ARGUMENTS` and classify:

| Complexity | Criteria | Planning Depth | Agent Strategy |
|------------|----------|----------------|----------------|
| **TRIVIAL** | Single file, < 50 lines, clear fix | Minimal | Direct execution |
| **SIMPLE** | 1-3 files, well-defined scope | Light | Single agent |
| **MODERATE** | 3-7 files, cross-cutting concern | Standard | 2-3 agents |
| **COMPLEX** | 7+ files, multi-layer changes | Full SPARC | 3-5 agents |
| **EPIC** | System-wide, architectural change | Extended SPARC | 5+ agents (swarm) |

### 0.3 Detect Task Domain
Map task to relevant skill clusters:

```yaml
domain_detection:
  product:
    triggers: [product, sku, variant, configurable, simple, product type, product values, super_attribute]
    skills: [unopim-domain, unopim-data, unopim-app]
    docs: [patterns-domain.md → Product Domain, patterns-data-external.md → Product Model]
    key_files:
      - packages/Webkul/Product/src/Models/Product.php
      - packages/Webkul/Product/src/Repositories/ProductRepository.php
      - packages/Webkul/Product/src/Type/AbstractType.php
      - packages/Webkul/Admin/src/Http/Controllers/Catalog/ProductController.php
      - packages/Webkul/Admin/src/DataGrids/Catalog/ProductDataGrid.php

  attribute:
    triggers: [attribute, family, group, option, swatch, attribute_type, filterable, translatable]
    skills: [unopim-domain, unopim-data, unopim-infra]
    docs: [patterns-domain.md → Attribute Domain]
    key_files:
      - packages/Webkul/Attribute/src/Models/Attribute.php
      - packages/Webkul/Attribute/src/Repositories/AttributeRepository.php
      - packages/Webkul/Admin/src/Http/Controllers/Catalog/AttributeController.php

  category:
    triggers: [category, tree, nested set, parent, _lft, _rgt, category_field]
    skills: [unopim-domain, unopim-data]
    docs: [patterns-domain.md → Category Domain]
    key_files:
      - packages/Webkul/Category/src/Models/Category.php
      - packages/Webkul/Category/src/Repositories/CategoryRepository.php
      - packages/Webkul/Admin/src/Http/Controllers/Catalog/CategoryController.php

  channel_locale:
    triggers: [channel, locale, currency, i18n, translation, multi-language, locale_specific, channel_specific]
    skills: [unopim-data, unopim-middleware, unopim-domain]
    docs: [patterns-data-external.md → Channel/Locale Models, patterns-middleware.md → Locale Middleware]
    key_files:
      - packages/Webkul/Core/src/Models/Channel.php
      - packages/Webkul/Core/src/Models/Locale.php
      - packages/Webkul/Admin/src/Http/Controllers/Settings/ChannelController.php

  auth_permissions:
    triggers: [permission, role, acl, bouncer, auth, guard, session, login, passport, oauth, api key, scope]
    skills: [unopim-middleware, unopim-app]
    docs: [patterns-middleware.md → Auth/Bouncer, patterns-application.md → ACL]
    key_files:
      - packages/Webkul/User/src/Http/Middleware/Bouncer.php
      - packages/Webkul/AdminApi/src/Http/Middleware/ScopeMiddleware.php
      - packages/Webkul/Admin/src/Config/acl.php
      - packages/Webkul/User/src/Models/Admin.php
      - packages/Webkul/User/src/Models/Role.php

  data_transfer:
    triggers: [import, export, csv, excel, job, batch, queue, data_transfer]
    skills: [unopim-domain, unopim-infra]
    docs: [patterns-domain.md → DataTransfer Domain]
    key_files:
      - packages/Webkul/DataTransfer/src/
      - packages/Webkul/Admin/src/Http/Controllers/Settings/DataTransfer/

  datagrid:
    triggers: [datagrid, grid, table, filter, column, sort, paginate, mass action, export grid]
    skills: [unopim-infra, unopim-app, unopim-client]
    docs: [patterns-infrastructure.md → DataGrid, patterns-client-designsystem.md → DataGrid Filter]
    key_files:
      - packages/Webkul/DataGrid/src/DataGrid.php
      - packages/Webkul/Admin/src/DataGrids/

  frontend_ui:
    triggers: [vue, blade, component, ui, form, modal, dropdown, dark mode, tailwind, icon, template, page]
    skills: [unopim-client]
    docs: [patterns-client-designsystem.md]
    key_files:
      - packages/Webkul/Admin/src/Resources/views/components/
      - packages/Webkul/Admin/src/Resources/assets/js/app.js
      - packages/Webkul/Admin/tailwind.config.js

  api:
    triggers: [api, rest, endpoint, v1, json, oauth, token, api controller]
    skills: [unopim-app, unopim-middleware]
    docs: [patterns-application.md → API Controllers/Routes, patterns-middleware.md → API Auth]
    key_files:
      - packages/Webkul/AdminApi/src/Http/Controllers/API/
      - packages/Webkul/AdminApi/src/Routes/V1/

  database:
    triggers: [migration, schema, query, grammar, json_extract, eloquent, model, repository, mysql, postgresql]
    skills: [unopim-data, unopim-infra]
    docs: [patterns-data-external.md → Database/Repository]
    key_files:
      - packages/Webkul/Core/src/Helpers/Database/GrammarQueryManager.php
      - packages/Webkul/Core/src/Eloquent/Repository.php

  event_history:
    triggers: [event, listener, dispatch, history, audit, version, webhook, notification]
    skills: [unopim-infra, unopim-app]
    docs: [patterns-infrastructure.md → Events/History]
    key_files:
      - packages/Webkul/HistoryControl/src/
      - packages/Webkul/Notification/src/
      - packages/Webkul/Webhook/src/

  magic_ai:
    triggers: [ai, openai, groq, gemini, ollama, llm, translate, magic, content generation]
    skills: [unopim-domain]
    docs: [patterns-domain.md → MagicAI Domain]
    key_files:
      - packages/Webkul/MagicAI/src/

  testing:
    triggers: [test, pest, phpunit, playwright, e2e, spec, coverage, fixture]
    skills: [unopim-patterns]
    key_files:
      - tests/
      - packages/Webkul/*/tests/

  bug_fix:
    triggers: [bug, fix, error, broken, crash, issue, debug, 500, 404, 403, exception]
    skills: [relevant domain skills based on error context]
    strategy: Read error → identify domain → load domain skill → fix

  security:
    triggers: [security, xss, csrf, injection, header, cors, sanitize, purify]
    skills: [unopim-middleware]
    docs: [patterns-middleware.md → SecureHeaders]

  channel_syndication:
    triggers: [channel adapter, syndication, sync, sync engine, salla, shopify, amazon, ebay, magento, noon, woocommerce, easyorders, marketplace, connector, conflict, bidirectional, field mapping, channel mapping, webhook signature, hmac, oauth callback, channel oauth]
    skills: [unopim-domain, unopim-data, unopim-app, unopim-middleware]
    docs: [docs/EPIC-001-COMPLETION-SUMMARY.md, docs/adapter-implementation-template.md, docs/api-contracts.md → Channel APIs]
    key_files:
      - packages/Webkul/ChannelConnector/src/Adapters/AbstractChannelAdapter.php
      - packages/Webkul/ChannelConnector/src/Contracts/ChannelAdapterContract.php
      - packages/Webkul/ChannelConnector/src/ValueObjects/SyncResult.php
      - packages/Webkul/ChannelConnector/src/ValueObjects/BatchSyncResult.php
      - packages/Webkul/ChannelConnector/src/ValueObjects/ConnectionResult.php
      - packages/Webkul/ChannelConnector/src/ValueObjects/RateLimitConfig.php
      - packages/Webkul/ChannelConnector/src/Services/
      - packages/Webkul/ChannelConnector/src/Jobs/
      - packages/Webkul/Salla/src/
      - packages/Webkul/Shopify/src/
      - packages/Webkul/Amazon/src/
      - packages/Webkul/Ebay/src/
      - packages/Webkul/Magento2/src/
      - packages/Webkul/Noon/src/
      - packages/Webkul/WooCommerce/src/
      - packages/Webkul/EasyOrders/src/

  tenant_isolation:
    triggers: [tenant, multi-tenant, tenancy, tenant_id, tenant scope, tenant guard, isolation, cross-tenant]
    skills: [unopim-data, unopim-middleware, unopim-app]
    docs: [docs/SECURITY_AUDIT_API_TENANT_ISOLATION.md, docs/TENANT_ISOLATION_SECURITY_AUDIT.md, docs/ROUTE_MIDDLEWARE_TENANT_AUDIT.md, docs/SECURITY_AUDIT_FILE_STORAGE.md, tests/docs/tenant-testing.md]
    key_files:
      - packages/Webkul/Tenant/src/Eloquent/TenantAwareBuilder.php
      - packages/Webkul/Tenant/src/Auth/TenantPermissionGuard.php
      - packages/Webkul/Tenant/src/Traits/TenantTesting.php
      - packages/Webkul/Tenant/src/Filesystem/
      - packages/Webkul/Tenant/src/Cache/
      - packages/Webkul/Tenant/src/Http/

  pricing:
    triggers: [pricing, price rule, price list, currency conversion, channel price, markup, markdown, pricing observer]
    skills: [unopim-domain, unopim-infra]
    docs: [patterns-domain.md → Product]
    key_files:
      - packages/Webkul/Pricing/src/Models/
      - packages/Webkul/Pricing/src/Services/
      - packages/Webkul/Pricing/src/Observers/
      - packages/Webkul/Pricing/src/ValueObjects/
```

---

## PHASE 1: SPECIFICATION (S in SPARC)

### 1.1 Output Task Understanding
```markdown
## Task Analysis

**Request:** $ARGUMENTS

**Classification:**
- Complexity: [TRIVIAL|SIMPLE|MODERATE|COMPLEX|EPIC]
- Domain: [detected domains]
- Estimated Scope: [packages/files affected]

**Activated Skills:**
- [List relevant /unopim-* skills that will be loaded]

**Agent Strategy:**
- Primary: [main agent type]
- Support: [supporting agents if needed]
```

### 1.2 Clarifying Questions (If Needed)
Only ask if critical ambiguity exists. Use AskUserQuestion tool:
```markdown
**Clarification Needed:**
1. [Specific question about scope/requirements]
> Reply with answers or say "proceed with assumptions"
```

### 1.3 Requirements Extraction
```markdown
**Functional Requirements:**
- [ ] FR1: [requirement]
- [ ] FR2: [requirement]

**Non-Functional Requirements:**
- [ ] NFR1: [performance/security/cross-DB compat]

**Constraints:**
- Must follow: [patterns from activated skills]
- Must not break: [existing functionality]
- DB compat: [MySQL + PostgreSQL via GrammarQueryManager]

**Success Criteria:**
- [ ] SC1: [how we verify it works]
```

---

## PHASE 2: PSEUDOCODE (P in SPARC)

### 2.1 Execution Plan
```markdown
## Execution Plan

### Step 1: [Phase Name]
- INTENT: What this achieves
- INPUT: What we need (files to read)
- OUTPUT: What we produce (files to create/modify)
- PATTERN: Which skill pattern applies
- VALIDATION: How we verify

### Step 2: [Phase Name]
...
```

### 2.2 Risk Assessment
```markdown
**Risk Matrix:**
| Risk | Likelihood | Impact | Mitigation |
|------|------------|--------|------------|
| [Risk] | Low/Med/High | Low/Med/High | [Strategy] |
```

---

## PHASE 3: ARCHITECTURE (A in SPARC)

### 3.1 File Impact Analysis
```markdown
## Architecture Impact

**Files to CREATE:**
- `packages/Webkul/{Package}/src/path/File.php` - [purpose]

**Files to MODIFY:**
- `packages/Webkul/{Package}/src/path/File.php` - [changes needed]

**Files to READ (Context):**
- `packages/Webkul/{Package}/src/path/File.php` - [why needed]

**Config to UPDATE:**
- `packages/Webkul/{Package}/src/Config/*.php` - [what changes]

**Migrations:**
- [If DB schema changes needed]
```

### 3.2 UnoPim Architecture Checklist
```markdown
**Architecture Compliance:**
- [ ] Model implements Contract interface
- [ ] Repository extends Webkul\Core\Eloquent\Repository
- [ ] Controller uses constructor injection
- [ ] Routes follow naming: admin.{module}.{resource}.{action}
- [ ] ACL entry added if new permission needed
- [ ] Events dispatched: {domain}.{entity}.{action}.{before|after}
- [ ] GrammarQueryManager used for raw SQL (no MySQL-specific)
- [ ] HistoryAuditable + HistoryTrait if entity needs versioning
- [ ] TranslatableModel if entity has translatable fields
- [ ] Dark mode supported (dark: Tailwind variants)
- [ ] Blade uses <x-admin::*> components
```

### 3.3 Test Strategy
```markdown
**Testing Approach:**
- Pest PHP: [what to test, which suite]
- Playwright E2E: [if UI changes, which spec]
- Run: `./vendor/bin/pest --parallel`
```

---

## PHASE 4: REFINEMENT LOOP (R in SPARC)

### 4.1 Implementation Protocol

For each step in the execution plan:

```markdown
### Executing Step [N]: [Name]

**Reading:** [files being analyzed]
**Skill Applied:** [/unopim-* pattern being followed]

[Implementation code/changes]

**Checkpoint:**
- [ ] Follows /unopim-{layer} patterns
- [ ] No breaking changes to existing code
- [ ] Cross-DB compatible (MySQL + PostgreSQL)
- [ ] Types/contracts correct
```

### 4.2 Pattern Verification
After each significant change verify against the activated skill:

```yaml
verification:
  data_layer:
    - GrammarQueryManager for raw SQL? (not MySQL-specific)
    - Contract interface on models?
    - Repository pattern for data access?
    - Product values JSON structure preserved?

  infrastructure:
    - ServiceProvider registered correctly?
    - DataGrid columns/actions follow conventions?
    - Events dispatched with before/after pairs?
    - HistoryTrait if auditable entity?

  domain:
    - Type instance pattern for products?
    - Nested set methods for categories?
    - Attribute scope resolution correct?
    - Bouncer permissions checked?

  application:
    - Controller extends correct base?
    - Route naming convention followed?
    - ACL entry exists for new permission?
    - Form Request validation rules correct?
    - Event dispatching around CRUD?

  middleware:
    - Auth guard correct (admin vs api)?
    - SecureHeaders not bypassed?
    - Locale/channel validation in place?

  client:
    - <x-admin::*> Blade components used?
    - dark: variants on all UI elements?
    - VeeValidate on all forms?
    - Icon from unopim-admin font?
```

### 4.3 Self-Correction Protocol
If error encountered:
```markdown
**Issue Detected:**
- Error: [description]
- Location: [file:line]

**Root Cause:** [analysis using skill knowledge]
**Correction:** [fix applied]
```

### 4.4 Mandatory Gate Invocation (HARD STOP)
Before transitioning to Phase 5, evaluate:

```yaml
must_invoke_g_strict_if_any_true:
  - complexity in [COMPLEX, EPIC]
  - touched_packages intersects [ChannelConnector, Tenant, Pricing, AdminApi, Salla, Shopify, Amazon, Ebay, Magento2, Noon, WooCommerce, EasyOrders]
  - new migration created
  - new API route created
  - new Bouncer/ACL permission added
  - new Vue component or Blade view created
  - command was invoked as /go strict $TASK
```

If any condition is true: **execute `STRICT REVIEW GATE` (defined below) and BLOCK Phase 5 until APPROVED.**

If all conditions are false (TRIVIAL/SIMPLE in-package tweak): run abbreviated check (Pint + targeted Pest) and proceed.

```markdown
## Gate Decision
- Conditions matched: [list]
- Gate required: YES/NO
- [If YES] Invoking G-STRICT now...
```

---

## PHASE 5: COMPLETION (C in SPARC)

### 5.1 Deliverables Summary
```markdown
## Completion Report

**Task:** $ARGUMENTS
**Status:** COMPLETED

**Changes Made:**
| File | Action | Description |
|------|--------|-------------|
| [path] | Created/Modified | [brief description] |

**Patterns Applied:**
- /unopim-{layer}: [how it was applied]

**Quality Checks:**
- [ ] Follows UnoPim architecture patterns
- [ ] Cross-DB compatible (GrammarQueryManager)
- [ ] No TypeScript/PHP errors
- [ ] Dark mode supported
- [ ] ACL permissions correct
- [ ] Events dispatched
```

### 5.2 Verification Commands
```markdown
**To Verify:**
```bash
# PHP tests
./vendor/bin/pest --parallel

# E2E tests (if UI changes)
cd tests/e2e-pw && npx playwright test

# Lint
./vendor/bin/pint --test

# Clear caches after config changes
php artisan config:clear && php artisan cache:clear
```
```

### 5.3 Follow-ups
```markdown
**Suggested Next Steps:**
1. [Related improvement or test coverage]
2. [Migration to run if schema changed]
3. [Cache to clear or config to publish]
```

---

## ADAPTIVE EXECUTION MODES

### Mode: TRIVIAL/SIMPLE
Skip phases 2-3, execute directly:
```markdown
## Quick Fix: $ARGUMENTS
**Skill:** /unopim-{domain}
[Read → Analyze → Fix → Verify]
**Done:** [Summary of change]
```

### Mode: MODERATE
Abbreviated SPARC (combine phases 2-3):
```markdown
## Task: $ARGUMENTS
### Plan & Architecture
[Combined planning with file impact]
### Implementation
[Execution with checkpoints]
### Done
[Summary]
```

### Mode: COMPLEX/EPIC
Full SPARC with parallel agents via Task tool:
```markdown
## Complex Task: $ARGUMENTS

### Phase 1: Specification [full]
### Phase 2: Pseudocode [full]
### Phase 3: Architecture [full]
### Phase 4: Implementation
**Agent Coordination (parallel via Task tool):**
- Task("Backend", "...", "coder")      → [model/repo/controller]
- Task("Frontend", "...", "coder")     → [blade/vue/tailwind]
- Task("DataGrid", "...", "coder")     → [grid implementation]
- Task("Tests", "...", "tester")       → [pest/playwright]
- Task("Review", "...", "reviewer")    → [quality check]
### Phase 5: Completion [full report]
```

---

## SKILL ACTIVATION PROTOCOL

### Progressive Loading
Only load skills when their domain is detected:

```yaml
activation_rules:
  - trigger: "product|attribute|category|family"
    load: /unopim-domain (read completely, apply type/repo/model patterns)

  - trigger: "controller|route|acl|menu|api"
    load: /unopim-app (read completely, apply controller/route/ACL patterns)

  - trigger: "vue|blade|tailwind|form|modal|icon|dark mode"
    load: /unopim-client (read completely, apply component/design system patterns)

  - trigger: "model|repository|query|grammar|json|eloquent|migration"
    load: /unopim-data (read completely, apply grammar/model/repo patterns)

  - trigger: "datagrid|event|history|concord|provider|theme"
    load: /unopim-infra (read completely, apply DataGrid/event/provider patterns)

  - trigger: "auth|bouncer|permission|middleware|passport|locale|security"
    load: /unopim-middleware (read completely, apply auth/security patterns)

  - trigger: complex task spanning multiple layers
    load: /unopim-patterns (master reference for cross-layer coordination)
```

---

## AGENT PERSONAS (Multi-Agent for COMPLEX/EPIC)

```yaml
agents:
  laravel-architect:
    type: "coder"
    role: Orchestrator for complex multi-package changes
    strengths: System design, cross-package coordination, Concord modules

  backend-engineer:
    type: "coder"
    role: PHP/Laravel specialist
    strengths: Models, repositories, controllers, services, migrations, queues

  frontend-engineer:
    type: "coder"
    role: Vue.js 3 / Blade / Tailwind specialist
    strengths: Components, forms, DataGrid UI, dark mode, responsive design

  database-architect:
    type: "coder"
    role: Schema & query specialist
    strengths: Migrations, GrammarQueryManager, JSON values, Eloquent optimization

  api-engineer:
    type: "coder"
    role: REST API specialist
    strengths: AdminApi controllers, Passport, resources, ValueSetter

  test-engineer:
    type: "tester"
    role: Pest PHP & Playwright specialist
    strengths: Unit tests, feature tests, E2E specs, fixtures

  code-reviewer:
    type: "reviewer"
    role: Quality assurance & pattern compliance
    strengths: Architecture review, security audit, pattern verification

  explorer:
    type: "Explore"
    role: Codebase reconnaissance
    strengths: Finding patterns, locating files, understanding existing code

  channel-adapter-specialist:
    type: "coder"
    role: Channel adapter & syndication specialist
    strengths: AbstractChannelAdapter contract, OAuth flows (Salla/Shopify), webhook HMAC verification, rate limiting, idempotent sync, conflict resolution, ValueObjects, field mapping
    activated_for: [ChannelConnector, Salla, Shopify, Amazon, Ebay, Magento2, Noon, WooCommerce, EasyOrders, channel sync work]

  tenant-security-auditor:
    type: "reviewer"
    role: Multi-tenant isolation auditor
    strengths: TenantAwareBuilder global scope, BelongsToTenant trait, TenantPermissionGuard, cross-tenant leak detection, file storage tenancy, cache key tenancy
    activated_for: [Tenant package work, ANY new model with tenant_id, ANY new API route]
    blocks_completion: true (if tenant isolation violation detected)

  security-auditor:
    type: "reviewer"
    role: Application security gate
    strengths: OWASP top 10, OAuth credential storage (encrypted casts), webhook signature verification, ACL completeness, ScopeMiddleware coverage, secret leak detection in logs
    activated_for: [AdminApi, ChannelConnector, OAuth flows, webhook receivers]
    blocks_completion: true (if HIGH/CRITICAL finding)
```

### Agent Coordination Pattern
```markdown
**Swarm Activated:**
Orchestrator: @laravel-architect

Workers (parallel via Task tool):
- @backend-engineer   → [model + repo + controller + migration]
- @frontend-engineer  → [blade views + vue components + tailwind]
- @api-engineer       → [API controller + routes + resources]
- @test-engineer      → [pest tests + playwright specs]
- @code-reviewer      → [final review against /unopim-patterns]

Execution Order:
1. Explorer: Understand existing code (parallel reads)
2. Backend + Database: Model/repo/migration (parallel)
3. Application: Controllers + routes + ACL (depends on 2)
4. Frontend: Views + components (depends on 3)
5. API: API controller + routes (parallel with 4)
6. Tests: Write and run (depends on 3-5)
7. Review: Pattern compliance check (depends on all)
```

---

## UNOPIM-SPECIFIC RULES

### Product Values JSON - NEVER Manipulate Directly
```php
// WRONG: Direct JSON manipulation
$product->values['common']['sku'] = 'new-sku';

// CORRECT: Use Attribute model methods
$attribute->setProductValue($value, $productValues, $channel, $locale);
$attribute->getValueFromProductValues($values, $channel, $locale);

// CORRECT (API): Use ValueSetter facade
ValueSetter::setCommon($data['values']['common']);
ValueSetter::setLocaleSpecific($data['values']['locale_specific']);
```

### Category Tree - NEVER Manipulate _lft/_rgt
```php
// WRONG: Direct nested set manipulation
$category->_lft = 5;

// CORRECT: Use NodeTrait methods
$category->appendToNode($parent);
$category->prependToNode($parent);
Category::scoped([])->defaultOrder()->get()->toTree();
```

### Database Queries - ALWAYS Cross-DB Compatible
```php
// WRONG: MySQL-specific
DB::raw("JSON_EXTRACT(values, '$.common.sku')");

// CORRECT: GrammarQueryManager
$grammar = GrammarQueryManager::getGrammar();
$grammar->jsonExtract('values', 'common', 'sku');
```

### Events - ALWAYS Before/After Pairs
```php
// CORRECT pattern for any CRUD operation:
Event::dispatch('catalog.product.create.before');
$product = $this->productRepository->create($data);
Event::dispatch('catalog.product.create.after', $product);
```

### Tenant Isolation - EVERY Query MUST Be Tenant-Scoped
```php
// WRONG: unscoped query leaks across tenants
$products = Product::where('status', true)->get();

// CORRECT: model uses TenantAwareBuilder global scope automatically
// New models that hold tenant data MUST:
//   1. Add `tenant_id` column (FK, NOT NULL, indexed)
//   2. Use the Tenant package's BelongsToTenant trait OR register TenantScope global scope
//   3. Be explicitly listed in tenant fixtures + tenant-testing.md
// Cross-tenant access requires the explicit TenantPermissionGuard check.

// FORBIDDEN: bypassing the global scope without an audit trail
Product::withoutGlobalScope(TenantScope::class)->get(); // Only allowed in Console commands tagged @cross-tenant
```

### Channel Adapters - MUST Extend AbstractChannelAdapter
```php
// EVERY new channel adapter:
//   1. Extends Webkul\ChannelConnector\Adapters\AbstractChannelAdapter
//   2. Implements ChannelAdapterContract
//   3. Returns ValueObjects (SyncResult, BatchSyncResult, ConnectionResult) - never raw arrays
//   4. Honors RateLimitConfig via ::throttle() (do NOT bypass)
//   5. Logs failures via Log facade with channel context
//   6. Encrypts credentials at rest (use Laravel encrypter, never plain text in DB)
//   7. Idempotent syncProduct() - safe to retry
class MyAdapter extends AbstractChannelAdapter { /* ... */ }
```

### Webhooks - Inbound Channel Webhooks MUST Verify Signature
```php
// WRONG: trusting payload without HMAC verification
public function handle(Request $request) { $this->process($request->all()); }

// CORRECT: verify signature BEFORE any processing
public function handle(Request $request) {
    if (! $this->verifyHmac($request->header('X-Salla-Signature'), $request->getContent())) {
        abort(401);
    }
    $this->process($request->all());
}
```

### OAuth Credentials - NEVER Store Plaintext
```php
// WRONG
$connector->update(['access_token' => $token]);

// CORRECT - cast to encrypted in the model
protected $casts = ['access_token' => 'encrypted', 'refresh_token' => 'encrypted'];
```

---

## STRICT REVIEW GATE (MANDATORY BEFORE PHASE 5 COMPLETION)

### Gate G-STRICT - Hard Block, No Exceptions

Every COMPLEX/EPIC task AND any task touching `ChannelConnector`, `Tenant`, `Pricing`, or any adapter package MUST pass this gate. The gate is invoked from Phase 4.4 (immediately after implementation, before Phase 5).

```yaml
gate_g_strict:
  blocking: true
  override: forbidden_unless_user_explicitly_says "override gate"

  step_1_style:
    command: ./vendor/bin/pint --test
    must_be: zero violations on changed files
    on_fail: run ./vendor/bin/pint then re-test

  step_2_static_analysis:
    if_present: ./vendor/bin/phpstan analyse (or psalm)
    on_fail: fix all reported issues

  step_3_tests:
    command: ./vendor/bin/pest --parallel --filter="<touched packages>"
    must_be: 100% pass on touched packages
    must_include: at least one new test per new public method
    coverage_floor:
      ChannelConnector adapters: 85%
      Tenant package: 90%
      anything else new: 75%

  step_4_pattern_compliance:
    invoke: /unopim-review staged
    must_pass: ALL checklist items in Steps 1-9
    on_fail: cannot complete

  step_5_security_audit:
    if_touched: [ChannelConnector, Tenant, AdminApi, any adapter]
    checks:
      - tenant_id present on every new query/model
      - HMAC verification on every inbound webhook route
      - OAuth tokens cast to 'encrypted'
      - No raw credentials in logs (grep changed files for $token, $secret, $key in Log:: calls)
      - ACL permission added for every new route
      - ScopeMiddleware on every new API route

  step_6_design_system:
    if_touched: [Admin views, Vue components]
    checks:
      - <x-admin::*> Blade components used (no raw <button>/<input>/<select>)
      - dark: variant on EVERY color/bg/border utility
      - Icon from unopim-admin font (no inline SVG for standard icons)
      - VeeValidate on every form
      - i18n: every visible string via @lang() or trans()

  step_7_architecture_no_break:
    checks:
      - No cross-package concrete class import (must go via Contract)
      - No new MySQL-specific SQL (grep for IFNULL, GROUP_CONCAT, JSON_EXTRACT, ->>'$.')
      - Every new Model has Contract interface
      - Every new Repository extends Webkul\Core\Eloquent\Repository
      - Every new Controller delegates to Repository/Service
      - No business logic in Controllers
      - Routes named: admin.{module}.{resource}.{action}

  step_8_regression_guard:
    command: git diff master --stat | grep -E "(Product|Attribute|Category|User)" 
    if_changes_detected: |
      - Run full test suite, not just touched packages
      - Verify Product values JSON structure preserved
      - Verify ACL config not broken: php artisan acl:rebuild

  on_any_failure:
    halt: true
    report:
      - which step failed
      - exact failures
      - proposed fix
    do_not_proceed_to_phase_5
```

### Quick Reference - Gate Output Template
```markdown
## Strict Gate Result
| Check | Verdict | Notes |
|-------|---------|-------|
| Pint style | PASS/FAIL | |
| PHPStan | PASS/FAIL | |
| Pest tests | PASS/FAIL (X/Y) | |
| /unopim-review | PASS/FAIL | |
| Security (tenant/webhook/oauth) | PASS/FAIL | |
| Design system | PASS/FAIL | |
| Architecture invariants | PASS/FAIL | |
| Regression guard | PASS/FAIL | |

**Gate verdict:** APPROVED | BLOCKED
```

---

## COMMAND VARIANTS

| Command | Purpose |
|---------|---------|
| `/go $TASK` | Full orchestration (adaptive complexity) |
| `/go plan $TASK` | Specification + Architecture only (no code) |
| `/go analyze $TASK` | Deep analysis with risk assessment |
| `/go quick $TASK` | Force TRIVIAL mode (immediate execution) |
| `/go epic $TASK` | Force full SPARC with swarm coordination |
| `/go strict $TASK` | Force full SPARC + MANDATORY G-STRICT gate, no overrides — use for channel/tenant/pricing work |
| `/go review $TASK` | Skip implementation, only run G-STRICT against current diff |

---

## CRITICAL RULES

1. **ALWAYS** load relevant `/unopim-*` skill before implementing
2. **NEVER** skip domain classification
3. **ALWAYS** use GrammarQueryManager for raw SQL (MySQL + PostgreSQL + SQLite)
4. **ALWAYS** implement Contract interfaces on new models
5. **ALWAYS** use Repository pattern for data access
6. **ALWAYS** dispatch before/after events around CRUD
7. **ALWAYS** use `<x-admin::*>` Blade components (never raw HTML for standard UI)
8. **ALWAYS** support dark mode with `dark:` Tailwind variants
9. **NEVER** manipulate product values JSON directly (use Attribute methods)
10. **NEVER** manipulate category `_lft`/`_rgt` directly (use NodeTrait)
11. **NEVER** write MySQL-specific queries without grammar abstraction
12. **NEVER** bypass Bouncer/ScopeMiddleware authentication
13. **NEVER** save files to the project root (use package directories)
14. **SCALE** planning depth to task complexity
15. **CHECKPOINT** after each significant change against skill patterns
16. **ALWAYS** scope every new query/model to `tenant_id` — no unscoped reads of tenant-owned data
17. **ALWAYS** verify HMAC signature on inbound channel webhooks BEFORE processing payload
18. **ALWAYS** cast OAuth/API credentials with `'encrypted'` Eloquent cast — no plaintext secrets in DB or logs
19. **ALWAYS** extend `AbstractChannelAdapter` for any new channel; return ValueObjects, never raw arrays
20. **ALWAYS** make `syncProduct()` idempotent (safe to retry without duplicate side effects)
21. **ALWAYS** invoke G-STRICT gate before completion when touching ChannelConnector / Tenant / Pricing / any adapter / AdminApi
22. **NEVER** bypass `RateLimiter` / `AbstractChannelAdapter::throttle()` — channel rate limits are non-negotiable
23. **NEVER** skip `/unopim-review` invocation on COMPLEX/EPIC tasks — it is the final compliance gate

---

## INITIALIZATION COMPLETE

Ready to receive task via `/go $ARGUMENTS`

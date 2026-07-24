**Purpose**: AI's persistent knowledge base for project context and learnings
**Last Updated**: 2026-07-23

## Memory Maintenance Guidelines

### Structure Standards

- Entry Format: ### [ID] [Title (YYYY-MM-DD)] ✅ STATUS
- Required Fields: Date, Challenge/Decision, Solution, Key Learning
- Length Limit: 3-6 lines per entry (excluding sub-bullets)
- Status Indicators: ✅ COMPLETE, ⚠️ PARTIAL, ❌ BLOCKED

### File Management

- Archive Trigger: When file exceeds 500 lines or 6 months old
- Archive Format: `memory-YYYY-MM.md`
- New File: Start fresh with current date and carry forward only active decisions

---

## Project Memory Entries

### [001] Project Kickoff — Concept & Action Plan Finalized (2026-07-23) ✅ COMPLETE

**Challenge**: Design an integrated mining operations dashboard to replace 3 daily Excel reports (DPR, Daily Info Site, Fuel Report) sent via email, plus integrate procurement KPIs from SAP B1.

**Solution**: Created comprehensive concept document (v0.3, ~800 lines) with 8 modules, 18-table ERD, and detailed action plan (9 phases, 1,059 lines). Three-system architecture: SAP B1 → ARK-GS (procurement), arkfleet-next (equipment), ARKA MineOps (production) — all via REST API.

**Key Learning**: Equipment data should NOT be duplicated — arkfleet-next already has ~1,000 units with codes matching Excel. Procurement KPIs (PO Sent, GRPO, NPI) already synced from SAP B1 by ARK-GS. ARKA MineOps consumes both via API, focuses on production operations + unified dashboard.

---

### [002] External Integration Architecture (2026-07-23) ✅ COMPLETE

**Decision**: All external data via REST API (not shared DB). ARKA MineOps consumes:
- `GET /api/equipment` from arkfleet-next (cached 1h)
- `GET /api/kpi/po-sent`, `/grpo`, `/npi`, `/budget` from ARK-GS (cached 6h)

**Key Learning**: Redis caching + graceful degradation (show last known data when API down). Equipment stored as `equipment_id` FK reference only — no column duplication (unit_code, model, etc. read from API when needed for display).

---

### [003] Database Conventions Locked (2026-07-23) ✅ COMPLETE

**Conventions**: Tables in snake_case plural (`daily_entries`, `production_records`), FKs as `singular_id`, money as `decimal(14,2)`, all tables have `id` PK + timestamps. Business terms in Indonesian, code in English.

**Key Learning**: Calculation Engine computes MTD/YTD/PTD/SR/FCR/Achievement — never stored as raw columns. Single source of truth for operational numbers.
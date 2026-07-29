**Purpose**: Record technical decisions and rationale for future reference
**Last Updated**: 2026-07-29

# Technical Decision Records

## Recent Decisions

### Decision: CCR Hourly — Reuse daily_entries Header — 2026-07-29

**Context**: CCR Google Sheets track production per jam per alat (Limestone/Shalestone). Need minimal schema add-on.

**Decision**: Reuse `daily_entries` as header; store hourly cells in `hourly_production_records`; plan in `material_daily_plans`; extend `equipment_assignments` for column mapping.

**Rationale**: Gets workflow, PWA sync, and approve invalidation for free. OB/Coal stream unchanged — material is parallel child table.

---

### Decision: REST API for External Integrations — 2026-07-23

**Context**: ARKA MineOps needs equipment data from arkfleet-next and procurement KPIs from ARK-GS. Two options considered: shared database vs REST API.

**Options Considered**:

1. **Shared Database**: Direct MySQL read from arkfleet_next / ARK-GS schemas
   - ✅ Pros: Simple, real-time, no latency
   - ❌ Cons: Schema coupling, deployment coupling, security risk, harder to scale independently

2. **REST API**: HTTP calls with Redis caching
   - ✅ Pros: Decoupled, independent deploy/scale, clean boundaries, consistent pattern
   - ❌ Cons: Extra latency, needs fallback handling, API endpoint maintenance

**Decision**: REST API (Option 2)

**Rationale**: Clean separation of concerns. Each app owns its domain. API with Redis caching (TTL 1h for equipment, 6h for procurement) provides good performance while maintaining independence.

**Implementation**: `EquipmentApiService` + `ProcurementApiService` with Laravel HTTP Client, Redis cache, circuit breaker, graceful degradation.

**Review Date**: After Fase 3 MVP deployment

---

### Decision: Calculation Engine Terpusat — 2026-07-23

**Context**: Excel reports store MTD/YTD/PTD/Achievement as raw columns, leading to inconsistencies across reports.

**Options Considered**:

1. **Store aggregates**: Save MTD/YTD/PTD directly in tables (mirror Excel)
   - ✅ Pros: Fast reads, simple queries
   - ❌ Cons: Stale data, inconsistent across views, needs recalculation jobs

2. **Compute on-the-fly**: Calculate MTD/YTD/PTD from daily raw data
   - ✅ Pros: Always consistent, single source of truth
   - ❌ Cons: Heavier queries, needs Redis caching for performance

**Decision**: Compute on-the-fly with Redis caching (Option 2)

**Rationale**: Consistency is the #1 value proposition. Users currently deal with 3 different Excel files with potentially different formulas. A centralized calculation engine guarantees everyone sees the same numbers.

**Implementation**: `CalculationService` in `app/Services/`, cached aggregates in Redis, invalidated when daily data is approved.

**Review Date**: After Fase 2 (Daily Entry) completion

---

### Decision: PWA over Native App for Mobile — 2026-07-23

**Context**: Supervisors need to input data from phones at mining sites with poor signal.

**Options Considered**:

1. **Native app** (React Native / Flutter)
2. **PWA** (Progressive Web App)

**Decision**: PWA (Option 2)

**Rationale**: Faster to build (same React stack), no app store approval, installable, offline via Service Worker + IndexedDB. Native app can be considered later if PWA limitations become blockers.

**Implementation**: Service Worker, Web Manifest, IndexedDB for offline drafts, UUID-based idempotent sync.

**Review Date**: After Fase 5 (Mobile/PWA) deployment

---

### Decision: Multi-Site from Day One — 2026-07-23

**Context**: Initially scoped for Site 022C only, but PT. Arkananta operates across ~8 sites.

**Decision**: Multi-site architecture from the start — `sites` table, `site_id` FK on all relevant tables, site selector UI in navbar. Costs negligible extra effort upfront, avoids painful migration later.

**Implementation**: `sites` table seeded with all known project codes (022C, 021C, 017C, 011C, 025C, 026C, 023C, APS).

**Review Date**: N/A (architectural foundation)
---
name: job-criteria
description: The user's hard and soft criteria for a job posting to be worth recording as a discovered application. The job-search hunter agent reads this skill at the start of every session and uses it to filter and rank postings. **Replace the placeholder content below with your actual criteria before enabling the agent.**
when_to_use: Whenever an agent must rank or filter job postings. Combine with the `cv` skill for fit assessment.
---

# Job criteria

> **STARTER CONTENT — edit before enabling the job-search agent.** The hunter is intentionally conservative: any criterion that's missing from this file is treated as "no preference, skip the posting." Over-restrictive defaults are safer than over-permissive ones.

## Active search window

Open: `<set-once-active>`. Close: `<set-when-not-searching>`.

The job-search agent **must** check that today's date is within this window. If not, the agent exits immediately without searching. The window is the user's hard switch between "actively looking" and "not looking."

> Example: open `2026-05-01`, close `2026-08-31`.

## Compensation

- **Minimum total compensation:** `<value> <currency>` per year.
- **Acceptable currency list:** `<list>`. Postings denominated in any other currency are skipped unless the user has explicitly opted in.
- **Equity:** acceptable / required / declined.

> Example: 50,000 EUR/year minimum; EUR / USD only; equity acceptable but not required.

## Location & arrangement

- **Remote:** required / preferred / acceptable / declined.
- **Acceptable on-site / hybrid locations:** list of cities or regions.
- **Time-zone overlap requirement:** number of hours of overlap with `<your timezone>` per day.

> Example: remote required; if on-site/hybrid, only Skopje; min 4 hours overlap with CET.

## Role shape

- **Acceptable seniority levels:** Senior / Staff / Principal / Lead / Manager.
- **Declined seniority levels:** Junior / Mid / Director / VP+.
- **Acceptable team sizes:** small (< 20) / medium (20–200) / large (200+).
- **Acceptable funding stage:** seed / series A / series B / public / bootstrapped.

> Example: Senior, Staff, or Lead. Skip Junior, Mid, Director and above. Skip seed-stage.

## Company filters

- **Industry blocklist:** the agent always skips postings from these industries.
- **Industry preference:** weight up postings from these industries.
- **Company blocklist:** named companies to always skip (past employers, ethical objections, etc.).
- **Company preference:** named companies to always surface even if other criteria are borderline.

> Example: blocklist = gambling, defense, surveillance; preferred = open-source labs, devtool startups.

## Application channel

- **Acceptable application channels:** company career page / direct recruiter outreach / LinkedIn / job boards (list).
- **Declined channels:** any board the user explicitly avoids.

> Example: accept company career pages and direct recruiter outreach via Gmail. Skip generic boards (Indeed, Glassdoor) unless the posting links back to a company page.

## Hard rules

The agent must respect these without exception. They override every other heuristic.

- Never auto-apply.
- Never record a posting outside the active search window.
- Never include the user's CV verbatim in any external system.
- Never share salary expectations with anyone outside LifeOS.

## Output preferences

- **Status to use** when recording a discovered application: `discovered` (default) or `shortlisted` (when the posting matches every criterion above and ≥ 80% of CV core skills appear in the requirements).
- **Notes** to set on every recorded posting: a one-line rationale explaining why it passed the filter, e.g. `"Matches: Laravel, AWS, EU-remote, 60k EUR; concerns: 5y of TypeScript expected, you have 3."`

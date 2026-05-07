---
name: cv
description: The user's current curriculum vitae. Used by the job-search hunter agent to decide which postings actually fit the user's experience before recording them as discovered applications. **Replace the placeholder content below before enabling the job-search agent.**
when_to_use: Whenever an agent must reason about whether a posting matches the user's experience, seniority, or specialism. The job-search agent reads it once at the start of every session.
---

# CV

> **PLACEHOLDER — replace with your actual CV.** The job-search hunter agent treats this file as authoritative when deciding whether a posting fits. Empty or default content here will cause the agent to skip almost everything (which is the safe failure mode).

## Headline

A one-line summary of who you are professionally. The agent uses this to filter postings whose titles obviously don't match.

> Example: "Backend-leaning full-stack engineer, 8 years of experience, Laravel + React + AWS, last role tech lead at a fintech."

## Years of experience

A simple integer. The agent uses this to skip postings that explicitly require more or fewer years.

> Example: `8`

## Core skills

Bullet list. The agent does substring matching against posting requirements; list both technologies and competencies.

> Example:
> - Laravel, PHP 8.x, Symfony
> - React, TypeScript, Inertia.js
> - PostgreSQL, MySQL, Redis
> - AWS (ECS, RDS, S3, Lambda), Docker, Terraform
> - REST API design, OAuth2, OpenAPI
> - System design, performance tuning, on-call ownership
> - Tech leadership, mentoring, code review

## Domains / industries

Bullet list of industries you have direct experience in. Used to weight postings.

> Example:
> - Fintech (payment processing, KYC, compliance)
> - SaaS (multi-tenant, billing)
> - E-commerce

## Education

One or two lines. Used only when a posting explicitly demands a degree.

> Example: "BSc Computer Science, Ss. Cyril and Methodius University Skopje, 2017."

## Languages

ISO codes + proficiency. Used for postings with explicit language requirements.

> Example:
> - English — fluent (C2)
> - Macedonian — native
> - Serbian — conversational (B1)

## What I will not do

A short list of things the agent must rule out regardless of how the posting is described. The agent uses this as a hard filter.

> Example:
> - Pure frontend roles
> - Pure DevOps / SRE roles
> - Roles requiring on-site presence in jurisdictions other than Macedonia / EU
> - Crypto / web3 startups
> - Defense / surveillance industry

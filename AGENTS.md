# SADIQ — Agent Rules

SADIQ is a Syrian single-school management system.

Stack:
- Laravel 13
- PHP 8.3
- MySQL
- Sanctum
- Pest

Priority:
Correctness > Security > Data Integrity > Critical Flows > Tests > Refactoring.

Source of truth:
1. Current checked-out Git commit.
2. Approved SADIQ architecture/domain documents.
3. Never invent missing business requirements.

Before modifying code:
1. Inspect directly affected files.
2. Trace indirectly affected files.
3. Explain the smallest required change.
4. Do not modify anything outside the approved scope.

Never change without explicit approval:
- database cardinality
- schema design
- authorization rules
- data scope
- deletion/retention
- authentication/token policy
- academic-year rules
- API contract

Security:
For every protected endpoint verify:
- authentication
- authorization
- data scope

Do not expose or modify:
- .env
- secrets
- real student data
- production data

Dependencies:
Do not modify composer.lock, package-lock.json, package.json,
or composer.json unless dependency changes were explicitly requested.

Testing:
Run the smallest relevant Pest tests after a change.
Never claim a test passed unless you executed it.

Implementation:
Prefer the smallest maintainable change.
Avoid new architecture layers unless required.
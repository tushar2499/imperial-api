---
name: feedback-no-unrequested-features
description: Never add features (soft deletes, timestamps, traits, behaviors) not explicitly requested — implement exactly what the user asks, nothing more
metadata:
  type: feedback
---

Never add features beyond what the user explicitly requests. When asked to add `status` to a model, only add `status`. Do not also add `SoftDeletes`, extra traits, extra columns, or extra behaviors "because the guide template shows them" or "because it's a good practice."

**Why:** User explicitly called this out — asked only for status active/inactive on seat_plans, and I added `SoftDeletes` without permission. This caused extra migration work to undo.

**How to apply:** Before adding any model trait, column, or behavior, verify the user's words include it. If the guide template has it but the user didn't ask, leave it out. Ask if uncertain.

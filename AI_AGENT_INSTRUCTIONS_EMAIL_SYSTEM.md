# 🤖 AI Agent Instructions: Email Templates System

**Project:** Violet E-Commerce - Email Notification System  
**Framework:** Laravel 12.37 + Filament v4  
**Approach:** Scenario 3 (Hybrid Smart System)  
**Language:** Arabic (RTL Support)

---

## ⚠️ **CRITICAL RULES - READ FIRST:**

### **🔴 Rule 1: ALWAYS Check Compatibility**
Before installing ANY package:
1. Search for "package-name Filament 4 compatibility"
2. Check package's GitHub issues for Filament 4 support
3. Read package documentation for Laravel 12 support
4. If unsure, STOP and ask for clarification
5. **NEVER assume compatibility**

### **🔴 Rule 2: ALWAYS Check Existing System**
Before writing ANY code:
1. Use `view` tool to explore existing project structure
2. Check if similar functionality already exists
3. Review existing models, migrations, services
4. Check existing Filament resources
5. **NEVER duplicate existing code**

### **🔴 Rule 3: Documentation First**
For every technology/package:
1. Read OFFICIAL documentation FIRST
2. Check Laravel 12 docs: https://laravel.com/docs/12.x
3. Check Filament v4 docs: https://filamentphp.com/docs/4.x
4. Check package docs on GitHub
5. **NEVER guess or assume - READ DOCS**

### **🔴 Rule 4: Report After Every Step**
After completing each task:
1. Write detailed report of what was done
2. List technologies/packages used
3. Document problems encountered and solutions
4. Show file paths of created/modified files
5. Confirm tests passed (if applicable)

---

# 📋 **PHASE 0: Pre-Implementation Investigation**

## **Task 0.1: Project Structure Analysis**

**Instructions:**
1. Use `view` tool to examine current project structure
2. Document existing directories:
   - `app/Models/`
   - `app/Filament/Resources/`
   - `app/Services/`
   - `app/Notifications/`
   - `database/migrations/`
   - `resources/views/`
3. Check for existing email-related code
4. Check for existing notification system
5. List all existing Filament resources

**Report Format:**
```
TASK 0.1 COMPLETED
=================
Current Structure:
- Models found: [list]
- Filament resources found: [list]
- Existing notifications: [list]
- Existing email views: [list]

Findings:
- [Any relevant findings]

Next Step: Ready for Task 0.2
```

---

## **Task 0.2: Check Existing Email Configuration**

**Instructions:**
1. Use `view` tool to check `config/mail.php`
2. Check `.env` for existing mail configuration
3. Check if queue system is configured
4. Document current mail driver
5. Check if any email packages are already installed

**Report Format:**
```
TASK 0.2 COMPLETED
=================
Current Mail Configuration:
- Driver: [current driver]
- Queue: [configured/not configured]
- Existing packages: [list]

Findings:
- [Any relevant findings]

Recommendations:
- [If any changes needed]

Next Step: Ready for Phase 1
```

---

# 📋 **PHASE 1: Package Research & Selection**

## **Task 1.1: MJML Package Research**

**Instructions:**
1. **STOP - Do NOT install anything yet**
2. Search for "MJML Laravel Filament 4" compatibility
3. Research these packages:
   - `webnuvola/laravel-mjml`
   - `daylaborers/laravel-mjml`
   - `asahasrabuddhe/laravel-mjml`
4. For EACH package, check:
   - Last update date (must be 2024 or later)
   - GitHub issues mentioning "Filament" or "Laravel 12"
   - README for Laravel version support
   - Check if it works with PHP 8.3
5. Read spatie/mjml-php documentation
6. **DO NOT PROCEED until you confirm compatibility**

**Report Format:**
```
TASK 1.1 COMPLETED
=================
Package Research Results:

Package 1: webnuvola/laravel-mjml
- Last update: [date]
- Laravel 12 support: [YES/NO/UNCLEAR]
- Filament 4 compatible: [YES/NO/UNCLEAR]
- Documentation quality: [rating]
- GitHub issues reviewed: [count]
- Recommendation: [USE/AVOID/NEEDS TESTING]

[Repeat for other packages]

FINAL DECISION: [Package name with justification]

Evidence Links:
- [Link to compatibility proof]
- [Link to documentation]

Next Step: Ready for Task 1.2
```

---

## **Task 1.2: Email Service Provider Research**

**Instructions:**
1. Research Amazon SES compatibility with Laravel 12
2. Read official AWS SDK for PHP documentation
3. Check if `aws/aws-sdk-php` is compatible with Laravel 12
4. Verify it works with PHP 8.3
5. Check alternative: Mailgun, SendGrid compatibility
6. **Document your findings before installing**

**Report Format:**
```
TASK 1.2 COMPLETED
=================
Email Provider Research:

Amazon SES:
- Laravel 12 support: [confirmed/not confirmed]
- Package: aws/aws-sdk-php
- Latest version: [version]
- Compatible: [YES/NO]
- Documentation: [link]

Alternatives Considered:
- [List with compatibility status]

FINAL DECISION: [Provider name with justification]

Next Step: Ready for Task 1.3
```

---

## **Task 1.3: Additional Dependencies Check**

**Instructions:**
1. Check if project already has:
   - Queue system packages
   - Spatie packages (for file handling, etc.)
   - Any email-related packages
2. Use `bash_tool` to run: `composer show | grep -i mail`
3. Use `bash_tool` to run: `composer show | grep -i queue`
4. Document what's already installed
5. **DO NOT install duplicates**

**Report Format:**
```
TASK 1.3 COMPLETED
=================
Already Installed Packages:
- [package-name]: [version] - [purpose]

Need to Install:
- [package-name]: [version] - [purpose] - [compatibility confirmed: YES]

Conflicts Found:
- [if any]

Resolution:
- [how to handle conflicts]

Next Step: Ready for Phase 2
```

---

# 📋 **PHASE 2: Database Schema Design**

## **Task 2.1: Review Existing Database Structure**

**Instructions:**
1. Use `view` tool to examine existing migrations
2. Check for any email/notification related tables
3. Check existing User model structure
4. Document existing polymorphic relationships
5. Check naming conventions used in project
6. **IMPORTANT: Match existing conventions**

**Report Format:**
```
TASK 2.1 COMPLETED
=================
Existing Database Structure:
- Users table columns: [list relevant columns]
- Existing notification tables: [list]
- Polymorphic relationships: [list]
- Naming convention: [snake_case/camelCase]
- Timestamp format: [timestamps()/timestamp()]

Conventions to Follow:
- [list conventions]

Next Step: Ready for Task 2.2
```

---

## **Task 2.2: Design email_templates Table**

**Instructions:**
1. Based on existing conventions, design table structure
2. **DO NOT write migration yet**
3. Plan column names, types, indexes
4. Check if project uses UUIDs or auto-increment IDs
5. Check if project uses soft deletes
6. Document your design decisions

**Report Format:**
```
TASK 2.2 COMPLETED
=================
Table Design: email_templates

Columns Planned:
- id: [type] - [reason]
- name: [type, length] - [reason]
- slug: [type, unique] - [reason]
[... list all columns with justification]

Indexes Planned:
- [column]: [reason]

Foreign Keys:
- [if any]

Design Decisions:
- Using soft deletes: [YES/NO] - [reason]
- Using UUIDs: [YES/NO] - [reason]
- JSON columns for: [list] - [reason]

Next Step: Ready for Task 2.3
```

---

## **Task 2.3: Design email_logs Table**

**Instructions:**
1. Design email_logs table following same conventions
2. Plan polymorphic relationship (related_type, related_id)
3. Design indexes for query performance
4. Consider data retention policies
5. **DO NOT write migration yet**

**Report Format:**
```
TASK 2.3 COMPLETED
=================
Table Design: email_logs

[Similar format as Task 2.2]

Polymorphic Relationship:
- Morph name: "related"
- Related models: [User, Order, etc.]

Performance Considerations:
- Indexes on: [list with reasons]
- Partitioning: [needed/not needed]

Next Step: Ready for Task 2.4
```

---

## **Task 2.4: Design email_tracking_events Table (Optional)**

**Instructions:**
1. Decide if this table is needed
2. Consider if data can be stored in email_logs
3. Evaluate storage vs. query performance tradeoff
4. Document decision with reasoning

**Report Format:**
```
TASK 2.4 COMPLETED
=================
Decision: [CREATE TABLE / SKIP]

Reasoning:
- [Detailed explanation]

If Creating:
- [Table design similar to previous tasks]

If Skipping:
- Alternative approach: [explain]

Next Step: Ready for Phase 3
```

---

# 📋 **PHASE 3: Package Installation**

## **Task 3.1: Install MJML Package**

**Instructions:**
1. **STOP - Review Task 1.1 decision**
2. Read package installation documentation THOROUGHLY
3. Check for special Filament 4 configuration steps
4. Install using exact version from documentation
5. Run `composer update` if needed
6. Check for any conflicts
7. **DO NOT proceed if installation fails**

**Commands to run:**
```bash
# Read composer.json first
view /home/claude/composer.json

# Install package (after confirming compatibility)
bash_tool: composer require [package-name]:[version] --with-all-dependencies

# Verify installation
bash_tool: composer show [package-name]
```

**Report Format:**
```
TASK 3.1 COMPLETED
=================
Package Installed: [package-name]:[version]

Installation Process:
1. [Step by step what was done]

Conflicts Encountered:
- [if any, how resolved]

Dependencies Installed:
- [list of additional packages]

Configuration Published:
- [if config was published, where]

Verification:
- Package shows in composer.json: [YES/NO]
- Package shows in composer.lock: [YES/NO]
- Package is loadable: [tested with php artisan]

Next Step: Ready for Task 3.2
```

---

## **Task 3.2: Install AWS SDK (if using SES)**

**Instructions:**
1. **Review Task 1.2 decision**
2. Read AWS SDK documentation
3. Check Laravel 12 specific requirements
4. Install package
5. Verify installation

**Report Format:**
```
TASK 3.2 COMPLETED
=================
[Similar format as Task 3.1]

Next Step: Ready for Task 3.3
```

---

## **Task 3.3: Configure Packages**

**Instructions:**
1. Publish configuration files (if needed)
2. Read EACH config file documentation
3. Update `.env` with placeholder values
4. Document each configuration option
5. **DO NOT set real credentials yet**

**Report Format:**
```
TASK 3.3 COMPLETED
=================
Configuration Files Published:
- [file path]: [purpose]

.env Variables Added:
- [VARIABLE_NAME]=[placeholder] - [description]

Configuration Choices Made:
- [setting]: [value] - [reason from documentation]

Next Step: Ready for Phase 4
```

---

# 📋 **PHASE 4: Database Implementation**

## **Task 4.1: Create email_templates Migration**

**Instructions:**
1. **Review Task 2.2 design**
2. Read Laravel 12 migration documentation
3. Check existing migrations for style guide
4. Use `bash_tool` to create migration
5. Write migration following project conventions
6. Add proper indexes
7. **DO NOT run migration yet**

**Commands:**
```bash
# Create migration
bash_tool: php artisan make:migration create_email_templates_table

# View the created file
view [migration file path]
```

**Report Format:**
```
TASK 4.1 COMPLETED
=================
Migration Created: [file name]
Location: [full path]

Schema Implemented:
- Columns: [count]
- Indexes: [count]
- Foreign keys: [count]

Conventions Followed:
- [list conventions from Task 2.1]

Code Review Checklist:
✓ Column types match design
✓ Indexes added
✓ Down method implemented
✓ Follows project style

Next Step: Ready for Task 4.2
```

---

## **Task 4.2: Create email_logs Migration**

**Instructions:**
1. Similar process as Task 4.1
2. Pay special attention to polymorphic columns
3. Add indexes for query performance
4. **DO NOT run migration yet**

**Report Format:**
```
TASK 4.2 COMPLETED
=================
[Similar format as Task 4.1]

Polymorphic Relationship:
- Morph name: [name]
- Index on: [columns]

Next Step: Ready for Task 4.3
```

---

## **Task 4.3: Create Models**

**Instructions:**
1. Read existing models to understand project style
2. Check if project uses traits (HasFactory, SoftDeletes, etc.)
3. Create EmailTemplate model
4. Create EmailLog model
5. Define relationships
6. Define casts
7. **Follow existing model patterns exactly**

**Report Format:**
```
TASK 4.3 COMPLETED
=================
Models Created:
1. EmailTemplate
   - Location: [path]
   - Relationships: [list]
   - Casts: [list]
   - Traits used: [list]

2. EmailLog
   - Location: [path]
   - Relationships: [list]
   - Casts: [list]
   - Traits used: [list]

Style Consistency:
✓ Matches existing model structure
✓ Uses same traits as similar models
✓ Follows fillable/guarded pattern
✓ Uses same docblock style

Next Step: Ready for Task 4.4
```

---

## **Task 4.4: Run Migrations & Test**

**Instructions:**
1. **BACKUP database first** (if not local)
2. Run migrations
3. Check for errors
4. Use tinker to test model creation
5. Rollback and re-run to test down() method
6. **Document any issues**

**Commands:**
```bash
# Run migrations
bash_tool: php artisan migrate

# Test in tinker
bash_tool: php artisan tinker
>>> EmailTemplate::create([...test data...])
>>> EmailLog::create([...test data...])
>>> exit

# Test rollback
bash_tool: php artisan migrate:rollback
bash_tool: php artisan migrate
```

**Report Format:**
```
TASK 4.4 COMPLETED
=================
Migration Status: [SUCCESS/FAILED]

Tables Created:
- email_templates: [verified with columns list]
- email_logs: [verified with columns list]

Testing Results:
- Model creation: [SUCCESS/FAILED]
- Relationships work: [tested how]
- Rollback works: [YES/NO]

Issues Encountered:
- [if any, with solutions]

Next Step: Ready for Phase 5
```

---

# 📋 **PHASE 5: MJML Template System**

## **Task 5.1: Research Filament 4 View System**

**Instructions:**
1. Read Filament 4 documentation on views
2. Check how project currently handles views
3. Understand where to place email templates
4. Check if Livewire views have special location
5. **DO NOT create files yet**

**Report Format:**
```
TASK 5.1 COMPLETED
=================
Filament 4 View System Understanding:
- View location convention: [path]
- Namespace convention: [explain]
- How Filament loads views: [explain]

Project-Specific Findings:
- Existing view structure: [describe]
- Blade components location: [path]
- Should we use components: [YES/NO with reason]

Recommendation:
- Email views location: [proposed path]
- Reasoning: [explain based on docs]

Next Step: Ready for Task 5.2
```

---

## **Task 5.2: Create Master MJML Template**

**Instructions:**
1. Read MJML documentation thoroughly
2. Read chosen MJML Laravel package docs
3. Understand how to integrate MJML with Blade
4. Create directory structure
5. Create master template with RTL support
6. **Test compilation to HTML**

**Report Format:**
```
TASK 5.2 COMPLETED
=================
Template Created: master.mjml.blade.php
Location: [full path]

MJML Features Used:
- [list MJML components used]
- RTL support implementation: [explain]

Blade Integration:
- @extends usage: [explain]
- @section defined: [list]
- Variables used: [list]

Testing:
- Compiled to HTML: [YES/NO]
- Compilation command: [command used]
- Output valid: [YES/NO]
- RTL verified: [how]

Issues Encountered:
- [if any, with solutions]

Next Step: Ready for Task 5.3
```

---

## **Task 5.3: Create Sample Customer Template**

**Instructions:**
1. Choose ONE simple template (e.g., Welcome Email)
2. Create MJML template extending master
3. Add Arabic content
4. Test RTL rendering
5. Test variable substitution
6. **DO NOT create all templates yet**

**Report Format:**
```
TASK 5.3 COMPLETED
=================
Template Created: welcome.mjml.blade.php
Location: [full path]

Content:
- Sections used: [list]
- Variables used: [list]
- MJML components: [list]

Testing:
- Compiles successfully: [YES/NO]
- RTL displays correctly: [YES/NO]
- Variables substitute: [YES/NO]
- Tested with data: [sample data used]

HTML Output:
- File size: [size]
- Renders in browser: [YES/NO]
- Mobile responsive: [tested how]

Next Step: Ready for Task 5.4
```

---

## **Task 5.4: Create Email Service**

**Instructions:**
1. Read existing project services structure
2. Understand service pattern used
3. Create EmailTemplateService
4. Implement MJML compilation logic
5. Implement variable replacement
6. Add error handling
7. **Test thoroughly**

**Report Format:**
```
TASK 5.4 COMPLETED
=================
Service Created: EmailTemplateService
Location: [full path]

Methods Implemented:
1. compile() - [description]
2. preview() - [description]
3. [list all methods]

Dependencies:
- [packages/classes used]

Testing:
- Compiled template with real data: [YES/NO]
- Error handling works: [tested scenarios]
- Performance: [compilation time]

Issues & Solutions:
- [if any]

Next Step: Ready for Phase 6
```

---

# 📋 **PHASE 6: Filament Admin Integration**

## **Task 6.1: Research Filament 4 Resource Structure**

**Instructions:**
1. **CRITICAL: Read Filament 4 Resource documentation**
2. Check existing Filament resources in project
3. Understand v3 → v4 breaking changes
4. Document resource file structure
5. Understand form builder changes in v4
6. **DO NOT start coding yet**

**Report Format:**
```
TASK 6.1 COMPLETED
=================
Filament 4 Resource Understanding:

Key Differences from v3:
- [list important changes]

Resource Structure:
- Base class: [name]
- Required methods: [list]
- Optional methods: [list]

Form Builder (v4 specific):
- Schema definition: [how it works]
- Field components: [available]
- Validation: [how to implement]

Table Builder (v4 specific):
- Column definition: [how it works]
- Filters: [how to implement]
- Actions: [how to implement]

Project's Existing Resources:
- [list 2-3 examples with patterns]
- Common patterns: [list]
- Naming conventions: [list]

Next Step: Ready for Task 6.2
```

---

## **Task 6.2: Create EmailTemplate Resource - Basic Structure**

**Instructions:**
1. Use artisan command to generate resource
2. **STOP after generation - review code**
3. Compare with existing resources
4. Ensure Filament 4 structure
5. **DO NOT add form/table yet**

**Commands:**
```bash
# Generate resource
bash_tool: php artisan make:filament-resource EmailTemplate

# Review generated files
view [resource file path]
view [resource pages directory]
```

**Report Format:**
```
TASK 6.2 COMPLETED
=================
Resource Generated: EmailTemplateResource
Location: [path]

Files Created:
- Resource: [path]
- Pages: [list]
- [any other files]

Generated Structure Review:
✓ Uses Filament 4 syntax
✓ Extends correct base class
✓ Has required methods
✓ Namespace correct

Comparison with Existing Resources:
- Matches pattern: [YES/NO]
- Needs adjustments: [list if any]

Next Step: Ready for Task 6.3
```

---

## **Task 6.3: Research Filament 4 Form Components**

**Instructions:**
1. **Read Filament 4 Form Builder documentation**
2. Especially focus on:
   - Tabs component
   - ColorPicker
   - FileUpload
   - KeyValue field
   - RichEditor (check if it's right for our use)
3. Check if components work with RTL
4. **Document what you learn - DO NOT CODE YET**

**Report Format:**
```
TASK 6.3 COMPLETED
=================
Filament 4 Form Components Research:

Available Components for Our Needs:
1. Tabs
   - Documentation: [link]
   - Usage: [explain]
   - RTL support: [YES/NO/PARTIAL]

2. ColorPicker
   - Documentation: [link]
   - Usage: [explain]
   - Format: [hex/rgb/etc]

3. FileUpload
   - Documentation: [link]
   - Storage: [how it works]
   - Preview: [available]

4. KeyValue
   - Documentation: [link]
   - Usage: [explain]
   - JSON handling: [how]

5. [Continue for other components]

RTL Considerations:
- [list RTL-specific concerns]
- [how Filament 4 handles RTL]

Recommendations:
- [which components to use]
- [any special configuration needed]

Next Step: Ready for Task 6.4
```

---

## **Task 6.4: Implement EmailTemplate Form - Tab 1 (Basic Info)**

**Instructions:**
1. Based on Task 6.3 research, implement ONLY Tab 1
2. Add: name, slug, type, category, description, is_active
3. Test slug auto-generation
4. Test validation
5. **STOP after Tab 1 - don't do other tabs yet**

**Report Format:**
```
TASK 6.4 COMPLETED
=================
Form Tab 1 Implemented: Basic Information

Fields Added:
- name: [component type] - [configuration]
- slug: [component type] - [configuration]
- type: [component type] - [options]
- [list all]

Features Implemented:
- Slug auto-generation: [how it works]
- Validation: [rules applied]
- Reactive fields: [which ones]

Testing:
- Form loads: [YES/NO]
- Validation works: [tested scenarios]
- Slug generation: [tested with: "مرحباً" → "mrhba"]

Screenshots/Output:
- [describe what you see]

Issues & Solutions:
- [if any]

Next Step: Ready for Task 6.5
```

---

## **Task 6.5: Implement EmailTemplate Form - Tab 2 (Content)**

**Instructions:**
1. Implement Tab 2: subject_ar, subject_en, available_variables
2. Test KeyValue field for variables
3. Ensure RTL works for Arabic subject
4. **Test saving and retrieval**

**Report Format:**
```
TASK 6.5 COMPLETED
=================
[Similar format as Task 6.4]

KeyValue Field Testing:
- Data storage format: [JSON/array]
- Retrieval works: [YES/NO]
- UI is user-friendly: [assessment]

RTL Testing:
- Arabic input displays correctly: [YES/NO]
- Saved data preserves Arabic: [YES/NO]

Next Step: Ready for Task 6.6
```

---

## **Task 6.6: Research Live Preview Implementation**

**Instructions:**
1. **This is complex - research thoroughly**
2. Read Filament 4 documentation on:
   - Custom field components
   - ViewField
   - Placeholder field
   - IFrame rendering
3. Research how to trigger MJML compilation on form change
4. Investigate security implications of rendering user HTML
5. **DO NOT implement yet - just research**

**Report Format:**
```
TASK 6.6 COMPLETED
=================
Live Preview Research:

Approach Options:
1. Using Placeholder field
   - Documentation: [link]
   - Pros: [list]
   - Cons: [list]
   - Feasibility: [HIGH/MEDIUM/LOW]

2. Using ViewField
   - [similar analysis]

3. Custom Livewire Component
   - [similar analysis]

RECOMMENDED APPROACH: [which one and why]

Implementation Complexity:
- Estimated difficulty: [1-10]
- Required knowledge: [list]
- Potential issues: [list]

Security Considerations:
- XSS risk: [assessment]
- Mitigation: [strategies]

Decision:
- Implement now: [YES/NO]
- If NO, implement in: [which phase]
- Reasoning: [explain]

Next Step: Ready for Task 6.7 or Phase 7
```

---

# 📋 **PHASE 7: Testing & Validation**

## **Task 7.1: Unit Tests for Models**

**Instructions:**
1. Read project's existing tests
2. Understand testing patterns used
3. Create tests for EmailTemplate model
4. Create tests for EmailLog model
5. Run tests
6. **Must achieve 100% model coverage**

**Report Format:**
```
TASK 7.1 COMPLETED
=================
Tests Created:
- EmailTemplateTest: [file path]
- EmailLogTest: [file path]

Test Coverage:
- EmailTemplate methods: [X/Y covered]
- EmailLog methods: [X/Y covered]
- Relationships tested: [list]

Tests Run:
bash_tool: php artisan test --filter=EmailTemplate

Results:
- Passed: [count]
- Failed: [count]
- Failures: [describe with solutions]

Next Step: Ready for Task 7.2
```

---

# 📋 **REPORTING TEMPLATE**

## **After EVERY Task, provide this report:**

```markdown
# TASK [X.Y] REPORT

## ✅ Status: [COMPLETED / IN PROGRESS / BLOCKED]

## 📝 What Was Done:
1. [Action 1]
2. [Action 2]
...

## 🔧 Technologies Used:
- [Technology/Package]: [Version] - [Purpose]
- [Documentation consulted]: [Links]

## 📁 Files Created/Modified:
- Created: [file path] - [purpose]
- Modified: [file path] - [what changed]

## 🧪 Testing Performed:
- [Test 1]: [Result]
- [Test 2]: [Result]

## ⚠️ Problems Encountered:
### Problem 1: [Description]
- **Symptom:** [What happened]
- **Investigation:** [What I checked]
- **Root Cause:** [What I found]
- **Solution:** [How I fixed it]
- **Documentation:** [Where I found the answer]

## 💡 Key Learnings:
- [Learning 1]
- [Learning 2]

## ⏭️ Next Step:
Ready for Task [X.Y+1] / Blocked waiting for [reason]

---
```

---

# 🎯 **PHASE COMPLETION CRITERIA**

## **Phase 1 Complete When:**
- [ ] All packages researched
- [ ] Compatibility confirmed with evidence
- [ ] Documentation links collected
- [ ] Decision made with justification

## **Phase 2 Complete When:**
- [ ] All tables designed
- [ ] Column types justified
- [ ] Indexes planned with reasoning
- [ ] No code written yet (design only)

## **Phase 3 Complete When:**
- [ ] Packages installed successfully
- [ ] No conflicts
- [ ] Configuration files published
- [ ] .env updated

## **Phase 4 Complete When:**
- [ ] Migrations created and run
- [ ] Models created
- [ ] Relationships working
- [ ] Tests pass

## **Phase 5 Complete When:**
- [ ] Master template compiles
- [ ] Sample template works
- [ ] RTL verified
- [ ] Service functional

## **Phase 6 Complete When:**
- [ ] Resource working in Filament
- [ ] Form saves data
- [ ] Table displays records
- [ ] All tabs functional

## **Phase 7 Complete When:**
- [ ] Tests written
- [ ] All tests pass
- [ ] Coverage > 80%
- [ ] Documentation complete

---

# ⚠️ **WHEN TO STOP AND ASK**

**STOP IMMEDIATELY and ask for clarification if:**

1. ❌ Package compatibility unclear
2. ❌ Documentation contradicts expected behavior
3. ❌ Breaking existing functionality
4. ❌ Security concern identified
5. ❌ Multiple approaches possible (need decision)
6. ❌ Test failures not understood
7. ❌ Filament 4 behavior different from docs
8. ❌ Unsure about project conventions

**Format for questions:**
```
🛑 CLARIFICATION NEEDED - Task [X.Y]

Question: [Clear question]

Context: [What I'm trying to do]

Research Done:
- [Source 1]: Says [X]
- [Source 2]: Says [Y]
- [Documentation]: Says [Z]

Options:
1. [Option A] - Pros: [...] Cons: [...]
2. [Option B] - Pros: [...] Cons: [...]

Recommendation: [Your suggestion with reasoning]

Waiting for: [Decision / Clarification / Permission]
```

---

# 🚀 **STARTING INSTRUCTION**

**When you're ready to begin:**

1. Confirm you've read ALL rules
2. Start with Task 0.1
3. Complete EACH task in order
4. Submit report after EACH task
5. Wait for approval before proceeding to next phase
6. **NEVER skip steps**

**Your first action should be:**
```
Starting Task 0.1: Project Structure Analysis
Reading existing project files...
```

---

**END OF INSTRUCTIONS**

Ready to begin? Start with Task 0.1 and provide your first report.

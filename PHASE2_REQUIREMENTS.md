# Phase 2: Advanced Features & Fixes

## 1. Question Navigator Issue Investigation

**Status**: Need clarification
**Description**: User reports question navigator numbering still has issues despite sequential implementation

### Current Implementation

- Navigator buttons display `{{ $index + 1 }}` (1, 2, 3, ...)
- Questions are randomized in backend via `ExamEngineService::getExamQuestions()`
- Buttons and slides use matching indices

### Needs Investigation

- Test if buttons display correctly as sequential despite randomization
- Check if issue is in JavaScript matching logic in `updateQuestionNav()`
- Verify session-based order storage isn't causing mismatches

---

## 2. Image Support for Questions & Options

**Status**: Not started
**Priority**: High
**Estimated Effort**: 6 hours

### Required Changes

#### 2.1 Database Migration

- Add `image_url` to `questions` table for question images
- Add `option_a_image`, `option_b_image`, `option_c_image`, `option_d_image`, `option_e_image` for option images

#### 2.2 Question Model Update

- Add new columns to `$fillable` in Question model
- Update validation in StoreQuestionRequest/UpdateQuestionRequest

#### 2.3 Admin Form Updates

- Add file upload inputs in `admin/questions/create.blade.php`
- Add file upload inputs in `admin/questions/edit.blade.php`
- Add image preview functionality

#### 2.4 Image Upload Controller Logic

- Create image upload handler in StudentExamController or new ImageController
- Store in `storage/app/public/questions/`
- Add validation (mime types, file size)
- Generate thumbnail URLs

#### 2.5 Student Exam View

- Display question image in `student/exams/take.blade.php` if exists
- Display option images alongside text options
- Responsive image sizing for mobile

#### 2.6 Result Page Update

- Display images in `student/exams/result.blade.php` for answer review

---

## 3. Comprehensive Weighted Grading System

**Status**: Partial (MC auto-scoring only)
**Priority**: High
**Estimated Effort**: 8 hours

### Current State

- MC questions auto-scored: `(correct / total) * 100`
- Essay questions set to `is_correct = null` for manual grading

### Required Enhancements

#### 3.1 Database Changes

- Add `weight_mc` (default 50) to exams table
- Add `weight_essay` (default 50) to exams table
- Add `score_essay` field to exam_attempts (if not exists)

#### 3.2 Weighted Scoring Formula

```
Final Score = ((MC_score * weight_mc) + (Essay_score * weight_essay)) / (weight_mc + weight_essay) * 100
```

#### 3.3 Essay Grading Interface

- Create admin route for grading essays: `admin/exams/{attempt}/essays`
- Display all essay questions for a student attempt
- Input field for each essay score (0-100)
- Validator for score range
- Show student answer, expected answer preview

#### 3.4 Update ExamEngineService

- Modify `submitExam()` to handle essay_score parameter
- Implement weighted calculation logic
- Store individual scores (score_mc, score_essay, final_score)

#### 3.5 Create EssayGraderController

- Method to list pending essay gradings
- Method to show grading form
- Method to save essay grades
- Integration with ExamEngineService

#### 3.6 Update Result Page

- Display MC score and Essay score separately
- Show overall weighted final score
- Display grade based on final score (A/B/C/D/F)

---

## 4. Mobile UI Optimization

**Status**: Partial (responsive base only)
**Priority**: Medium
**Estimated Effort**: 4 hours

### Current Implementation

- First-pass responsive design with Tailwind
- Flex/grid layouts work on mobile

### Required Enhancements

#### 4.1 Floating Action Button (FAB) for Mobile

- Replace traditional buttons on mobile screen (<768px)
- Floating button for:
    - Submit Exam (green, bottom-right)
    - Next Question (blue, with arrow indicator)
- Fixed position, doesn't scroll with content

#### 4.2 Question Navigator Adjustment

- On mobile: Collapse navigation to horizontal scroll or togglable dropdown
- Dropdown shows: "Question Navigator" button that expands/collapses a list
- Prevent layout shift when navigator opens/closes

#### 4.3 Mobile-Specific Improvements

- Larger touch targets (min 44px×44px)
- Remove unnecessary elements on mobile (exam info card can collapse)
- Optimized font sizes for readability
- Better spacing between form elements

#### 4.4 Dropdown System for Action Buttons

- Create reusable dropdown component in Blade
- Use on mobile for multiple actions
- Smooth transitions
- Close on outside click

---

## Implementation Priority

1. **Image Support** (2.0) - Required for visual questions
2. **Question Navigator Fix** (1.0) - Critical UX issue
3. **Weighted Grading** (3.0) - Required for fair assessment
4. **Mobile UI** (4.0) - Enhancement for better UX

---

## Technical Debt to Address

- Refactor JavaScript in take.blade.php (very large script section)
- Create separate JS files for exam logic
- Add error handling for image uploads
- Add test coverage for new features
- Performance optimization for loading multiple images

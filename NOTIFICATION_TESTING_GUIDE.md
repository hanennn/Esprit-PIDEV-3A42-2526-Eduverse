# Forum Notification System - Testing Guide

## Fixed Issues

### 1. ✅ Cascade Persist Error
**Problem**: Getting "cascade persist operations" error when students add comments
**Solution**: Removed `message_ref` from new comment notifications to avoid cascade issues. The `sujet` reference is sufficient to link to the discussion.

### 2. ✅ User Comparison Issues
**Problem**: Entity comparison using `===` failed when entities came from different contexts
**Solution**: Updated all user comparisons to use `getId()` method for reliable ID-based comparison

### 3. ✅ Missing Variable Definitions
**Problem**: `deleteMessage` method had undefined variables ($user, $sujet)
**Solution**: Properly defined all required variables at the start of the method

### 4. ✅ Duplicate Code Blocks
**Problem**: CSRF token validation was duplicated in delete method
**Solution**: Consolidated into a single validation block

---

## Three Notification Scenarios to Test

### Scenario 1: New Forum Post Notification
**When**: A teacher creates a new forum post (sujet)
**Who Gets Notified**: All students (users without ROLE_TEACHER or ROLE_ADMIN)
**Notification Type**: `new_forum`
**Message**: "Nouveau sujet du forum: '[titre]' par [Prénom Nom]"

**Testing Steps**:
1. Login as a teacher account
2. Go to Forum → Create new post
3. Fill in title and content, submit
4. Logout and login as a student account
5. Click on Notifications bell icon
6. ✅ You should see a notification about the new forum post

---

### Scenario 2: New Comment Notification
**When**: A student comments on a teacher's forum post
**Who Gets Notified**: The teacher who owns the post
**Notification Type**: `new_comment`
**Message**: "[Prénom Nom] a commenté sur votre sujet '[titre]'"

**Testing Steps**:
1. Login as a student account
2. Go to a forum post created by a teacher
3. Add a comment/message
4. Logout and login as the teacher who created that post
5. Click on Notifications bell icon
6. ✅ You should see a notification about the new comment

**Note**: Teachers don't get notified when they comment on their own posts

---

### Scenario 3: Comment Deleted Notification
**When**: A teacher deletes a student's comment on their own post
**Who Gets Notified**: The student whose comment was deleted
**Notification Type**: `comment_deleted`
**Message**: "Votre commentaire sur le sujet '[titre]' a été supprimé par le formateur."

**Testing Steps**:
1. Ensure a student has commented on a teacher's post (use Scenario 2 setup)
2. Login as the teacher who owns the post
3. Go to that forum post
4. Find the student's comment - you'll see a "Supprimer" (Delete) button
5. Click delete and confirm
6. Logout and login as the student whose comment was deleted
7. Click on Notifications bell icon
8. ✅ You should see a notification that their comment was deleted

**Note**: Students don't get notified when they delete their own comments

---

## Teacher Delete Permissions

Teachers can now delete comments on their own forum posts:
- ✅ Teachers see "Supprimer" button on ALL comments on their posts
- ✅ Students only see "Supprimer" on their own comments
- ✅ Admins can delete any comment

---

## Notification Features

### Notification Page
Access via: `/notification` or click the bell icon in the forum

**Features**:
- View all notifications (up to 50)
- Unread notifications highlighted with blue border
- Color-coded badges:
  - 🔵 Blue (info): New Forum
  - 🟢 Green (success): New Comment
  - 🟡 Yellow (warning): Comment Deleted
- "Voir" button to navigate directly to the forum post
- "Lu" button to mark individual notifications as read
- "Marquer tout comme lu" to mark all as read
- "Supprimer" to delete individual notifications

### Notification Badge
- Small red badge appears on bell icon showing unread count
- Updates automatically via AJAX
- Visible on both forum index and forum show pages

---

## Database Tables

### notification table structure:
- `id` - Primary key
- `message` - Notification text
- `type` - Type of notification (new_forum, new_comment, comment_deleted)
- `is_read` - Boolean flag
- `date_creation` - When notification was created
- `destinataire_id` - Foreign key to user (who receives the notification)
- `sujet_id` - Foreign key to sujet (nullable, with CASCADE delete)
- `message_ref_id` - Foreign key to message (nullable, currently not used for new comments)

---

## Troubleshooting

### Students not receiving new forum notifications?
1. Check student user roles: `SELECT id, Username, roles FROM user;`
2. Students should NOT have `ROLE_TEACHER` or `ROLE_ADMIN` in their roles array
3. The roles column typically contains: `["ROLE_USER"]` for students

### Notifications not showing?
1. Clear browser cache
2. Check notification count API: Visit `/notification/unread-count` in browser
3. Check database: `SELECT * FROM notification WHERE destinataire_id = [your_user_id];`

### Delete button not showing for teachers?
1. Verify teacher has `ROLE_TEACHER` in their roles
2. Verify teacher is the author of the sujet (not just any teacher)
3. Check browser console for JavaScript errors

---

## Code Changes Summary

### Files Modified:
1. `src/Service/NotificationService.php` 
   - Fixed user comparison to use IDs
   - Removed message_ref from new comment notifications
   
2. `src/Controller/ForumController.php`
   - Fixed deleteMessage method with proper variable definitions
   - Fixed user comparison to use IDs
   - Consolidated CSRF validation

3. `src/Entity/User.php`
   - Fixed syntax error (removed extra closing brace)

### Files Created:
1. `src/Entity/Notification.php`
2. `src/Repository/NotificationRepository.php`
3. `src/Service/NotificationService.php`
4. `src/Controller/NotificationController.php`
5. `templates/notification/index.html.twig`
6. Migration: `migrations/Version20260216001003.php`

---

## Next Steps

1. ✅ Test all three notification scenarios
2. ✅ Verify teacher can delete student comments on their posts
3. ✅ Verify notification badge updates correctly
4. ✅ Test marking notifications as read
5. ✅ Test across different user roles (student, teacher, admin)

---

Good luck with testing! 🚀

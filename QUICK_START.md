# Quick Start Guide - QC Finding Comments Feature

## 🚀 Deployment Steps

```bash
# Step 1: Update database
php artisan migrate

# Step 2: Clear cache
php artisan cache:clear
php artisan view:clear

# Step 3: Test
# Navigate to: /qc/findings/{any_finding_id}
# Scroll down → see "Komentar & Diskusi" card
```

---

## 📱 User Guide

### Adding a Comment
1. Go to Finding Detail page
2. Scroll to "Komentar & Diskusi" card
3. Type in textarea: "Temuan ini..."
4. Click "Kirim Komentar"
5. ✅ Comment appears instantly

### Replying to a Comment
1. Click "Balas" button on any comment
2. Reply form appears below that comment
3. Type reply: "Setuju, sudah..."
4. Click "Kirim Balasan"
5. ✅ Reply appears under parent comment (nested)

### Deleting Your Comment
1. Find your comment (you'll see "Hapus" button)
2. Click the red "Hapus" button
3. Confirm deletion
4. ✅ Comment removed

### When Finding is CLOSED
- ✅ Can view all comment history
- ❌ Cannot add new comments
- ❌ Cannot reply
- Message: "Temuan ini sudah ditutup. Anda dapat melihat riwayat..."

---

## 🔧 Developer Guide

### File Locations
```
app/Models/
  └─ QcFindingComment.php          ← Comment model
  
app/Http/Controllers/
  └─ QcFindingCommentController.php ← CRUD endpoints
  
database/migrations/
  └─ 2026_03_25_000001_create_...  ← Table schema
  
resources/views/findings/
  ├─ _comments_section.blade.php   ← Complete UI
  └─ show.blade.php                ← Uses partial
  
routes/
  └─ web.php                       ← 3 new routes
```

### Key Endpoints
```
POST   /qc/findings/{finding}/comments
DELETE /qc/findings/{finding}/comments/{comment}
GET    /qc/findings/{finding}/comments
```

### Working with Comments in Code

#### Get all root comments:
```php
$finding = QcFinding::with('mainComments.user', 'mainComments.replies.user')->find(1);
$rootComments = $finding->mainComments; // Collection
```

#### Get single comment with nested replies:
```php
$comment = QcFindingComment::with('user', 'replies.user')->find(1);
echo $comment->content;              // "Temuan ini..."
echo $comment->user->name;           // "John"
echo $comment->replies->count();     // 2
```

#### Create new comment:
```php
$comment = $finding->comments()->create([
    'user_id' => auth()->id(),
    'content' => 'Perlu tindak lanjut',
    'parent_comment_id' => null, // null = root, or comment ID for reply
]);
```

#### Delete comment:
```php
$comment->delete(); // Cascade deletes all replies
```

---

## 🎨 Customization

### Change max comment length (default 5000 chars):
```php
File: app/Http/Controllers/QcFindingCommentController.php
Line 18: 'content' => 'required|string|min:1|max:5000', // Change here
```

### Change comment form styling:
```blade
File: resources/views/findings/_comments_section.blade.php
Section: <style> at top
Classes to modify:
  - .comment-form
  - .comment-item
  - .reply-form
```

### Change load behavior:
```javascript
File: resources/views/findings/_comments_section.blade.php
Function: loadComments() [line ~80]
Modify fetch() call for custom behavior
```

---

## 🐛 Debugging

### Check if comments table exists:
```bash
php artisan tinker
>>> \Illuminate\Support\Facades\Schema::hasTable('qc_finding_comments')
=> true
```

### Check model relationships:
```bash
php artisan tinker
>>> $f = \Modules\QcComplaintSystem\Models\QcFinding::first()
>>> $f->comments()->count()
=> 5
```

### Check routes:
```bash
php artisan route:list | grep comment
```

### View network requests:
1. Open browser DevTools (F12)
2. Go to "Network" tab
3. Perform comment action
4. Watch for `/qc/findings/{id}/comments` requests

---

## 📋 Common Issues & Solutions

### Issue: Comment form not showing
**Cause**: Status might be CLOSED  
**Solution**: Change finding status to 'open' or 'in_review'

### Issue: Comments not saving
**Cause**: CSRF token missing  
**Solution**: Ensure `csrf-token` meta tag in layout

### Issue: Replies not showing
**Cause**: Eager loading not working  
**Solution**: Clear view cache: `php artisan view:clear`

### Issue: Delete button not appearing
**Cause**: You're not the comment author  
**Solution**: Only can delete your own comments (by design)

### Issue: Comments look broken/unstyled
**Cause**: CSS not loaded  
**Solution**: Ctrl+Shift+R (hard refresh), clear browser cache

---

## ✅ Testing Checklist

- [ ] Can add comment on OPEN finding
- [ ] Can add reply to comment
- [ ] Can reply to reply (nested)
- [ ] Can delete own comment
- [ ] Cannot delete others' comments
- [ ] Cannot comment on CLOSED finding
- [ ] Can view comments on CLOSED finding
- [ ] Comments show author name & time
- [ ] No page reload when adding comment
- [ ] Error handling works (invalid input)

---

## 📞 Support

**Questions about the feature?**
- Check `COMMENTS_FEATURE_DOCUMENTATION.md` for detailed docs
- Check `IMPLEMENTATION_SUMMARY.md` for architecture details
- Check controller/model files for code comments

**Want to enhance?**
- See "Future Enhancement Options" in COMMENTS_FEATURE_DOCUMENTATION.md
- Common: add editing, reactions, mentions, search

---

## 🎯 Performance Notes

**For small finding volumes** (< 1000 findings):
- Current implementation perfectly fine
- No performance concerns

**For large volumes** (> 10k findings with 100k+ comments):
- Add pagination: `$comments->paginate(20)`
- Add caching: `Cache::remember('finding_comments_' . $id, ...)`
- Archive old comments to separate table

---

Last Updated: 2026-03-25  
Version: 1.0  
Status: ✅ Production Ready

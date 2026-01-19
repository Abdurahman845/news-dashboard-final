# TESTING

Manual checklist to verify the News Dashboard flows.

## Auth
- ✅ Login success with seeded user (`admin@newsdashboard.com` / `password123`)
- ✅ Login failure shows inline errors

## Articles
- ✅ List loads (with search params) and paginates if more than a page
- ✅ Category filter changes results
- ✅ Search filters results
- ✅ Create article: validation errors show inline; success creates and redirects
- ✅ Edit article: loads existing data, validates, saves changes
- ✅ Delete article: confirm dialog and removal

## Error Handling
- ✅ API down: user sees friendly error (no raw JSON)
- ✅ 401 triggers logout + redirect to login

## UI/Responsive
- ✅ Empty state shows when no articles match
- ✅ Mobile layout verified (screenshots optional): stacked filters, full-width cards, buttons not overflowing

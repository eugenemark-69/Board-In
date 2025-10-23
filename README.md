# Board-In
Board-in Web aplication system

hey so i want to add  a new feature so i want to add a verrified tag so right now its simple landlord make a request and post the pic details of the bh and the admin aproav after that the students can see it and search it. i want a feature where that bh will be marked unverefied and can only be verified when they submit valid documents like id and busness permit and our team would personally go to the bh and make sure the photos are real and then we will mark tht bh as verified. so landlord see a bh in search.php then they click more details  and thats where a button to verrify your bh . anyways this is not all my files just tell me what you need , if you have questions freee to ask. think hard. think like what would a 20 yrs old veteran software engineer would do if given this task.
Key Questions First:

Verification Scope: Are we verifying the landlord or individual boarding houses?

I see you already have a landlords table with verification status
But you want per-BH verification, which makes sense (one landlord could have multiple properties)


Workflow:

Student sees listing → marked "unverified"
Landlord can request verification per BH
Landlord submits documents (ID, business permit, proof of ownership)
Admin reviews online
Your team does physical verification
BH gets "verified" badge


Current vs New System:

Currently: Listings go pending → admin approves → active
New: Listings go pending → admin approves → active (but unverified) → landlord requests verification → admin reviews docs → team verifies on-site → verified
File Changes Needed:

landlord/verify-bh.php - New file for landlords to request verification
admin/verify-bh-requests.php - Admin panel to review documents
admin/schedule-visit.php - Schedule on-site verification
Update search.php - Show verified badge
Update listing.php - Show verification status + "Request Verification" button
Update config.php - Add new tables to auto-installation

Before I create the code, please answer:

Should UNVERIFIED boarding houses still show in search, or only VERIFIED ones?
Can landlords edit their listing after verification is requested/approved?
What happens if verification is rejected - can they resubmit?
Should there be a verification fee?
this for the key questions 1  what do you think a senior software engineer would do, 2 yes but they will be marked unverified, 3 the basic legal documents that prove their bh is ligitimate, before you create questions 1 yes both would be available, 2 what do you think is good, 3 yes they can try again but an admin can block them or do something to that user if they just spam request, 4 no, also just edit the crud for the new sql tables or elemnts that you might add dont create a separate sql file
Per-BH verification makes more sense - A landlord might have one legitimate property and one sketchy one. Verify each independently.
Landlords should NOT be able to edit after verification is approved - Otherwise they could bait-and-switch. Lock the listing or require re-verification if they edit critical fields (price, amenities, photos).
Spam protection - Track rejection count per BH. After 3 rejections, require admin manual approval to resubmit.
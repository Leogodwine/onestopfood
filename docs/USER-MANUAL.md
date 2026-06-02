# One Stop Food — User Manual

**Food Order & Delivery System**  
Version 1.0 | Tanzania

---

## 1. Introduction

One Stop Food is a direct chef-to-customer food ordering and delivery platform serving Tanzania. The system connects **customers**, **chefs**, **travelers (droppers)**, and **administrators** through a single web application.

**Website:** https://www.onestopfood.co.tz  
**Support:** support@onestopfood.co.tz

---

## 2. Getting Started

### 2.1 Create an Account

You can register in three ways:

| Method | Best for |
|--------|----------|
| **Email & password** | Customers, chefs, and travelers who prefer a traditional account |
| **Google sign-in** | Quick sign-up; no password required |
| **Facebook sign-in** | Quick sign-up; no password required |

**Steps (email registration):**

1. Open the **Login** page.
2. Click **Register here!** or **Become a Chef** / **Become a Traveler**.
3. Fill in name, email, phone, role, and password.
4. Click **Create Account**.

**Steps (Google / Facebook):**

1. Click **Google** or **Facebook** on the login page.
2. Authorize the app with your social account.
3. On the **Complete Your Sign Up** page, enter your **phone number**.
4. Choose to receive a **6-digit verification code** by **email** or **phone**.
5. Enter the code and click **Verify & continue**.

> Social accounts do **not** use a password. Sign in always through Google or Facebook.

### 2.2 Sign In

- **Email users:** Enter email and password on the login page.
- **Social users:** Click **Google** or **Facebook**.
- **Admin users:** Must use email and password (social login is not allowed for admins).

### 2.3 Roles

| Role | Description |
|------|-------------|
| **Customer** | Browse meals, order food, track delivery, leave reviews |
| **Chef** | List meals, receive orders, prepare food, manage kitchen profile |
| **Traveler / Dropper** | Pick up and deliver orders to customers |
| **Admin** | Approves partners, monitors orders, manages platform settings |

---

## 3. Customer Guide

### 3.1 Browse & Order

1. Visit the **Home** page or **Browse Meals**.
2. Add meals to your **Cart**.
3. Go to **Checkout**.
4. Select or add a **delivery address** (City and District in Tanzania).
5. Choose a **payment method**:
   - M-Pesa (Vodacom)
   - Tigo Pesa
   - Airtel Money
   - Card
   - Cash on Delivery (COD)
6. Place your order and track it from **My Orders**.

### 3.2 Manage Addresses

- Go to **Dashboard → My Addresses**.
- Add, edit, or delete delivery locations.
- Set one address as **primary** for faster checkout.

### 3.3 Track Orders

Order statuses you may see:

| Status | Meaning |
|--------|---------|
| Pending | Order placed; waiting for chef |
| Accepted | Chef accepted the order |
| Preparing | Chef is cooking |
| Ready | Food is ready for pickup |
| Out for delivery | Traveler is delivering |
| Delivered | Order completed |
| Cancelled | Order was cancelled |

### 3.4 Reviews

After delivery, rate the **chef** and **traveler** from the order details page.

### 3.5 Become a Chef or Traveler

If you signed in with Google/Facebook as a customer and want to become a partner:

1. Open your **Customer Dashboard**.
2. Under **Become a Partner**, click **Become a Chef** or **Become a Traveler**.
3. Complete the **verification profile** (see Sections 4 and 5).
4. Wait for **admin approval**.

---

## 4. Chef Guide

### 4.1 Registration & Approval

1. Register as **Chef** (email or social sign-in with “Become a Chef”).
2. Complete the **Chef Verification & Onboarding** form at `/verify`.
3. Submit all required sections for admin review.
4. Wait for approval. Until approved, your dashboard shows **Account Pending Approval**.

### 4.2 Verification Sections

Complete all six tabs:

1. **Identity & Contact** — Date of birth, NIDA ID, nationality, gender, selfie (camera capture supported).
2. **Kitchen Location** — Street address, city, district (Tanzania regions), kitchen type, proof of kitchen, kitchen photos (minimum 2 for home kitchens).
3. **Experience & Skills** — Bio, years of experience, specialties, heritage story.
4. **Legal Documents** — Food safety certificate, business license, food handling permit, health inspection (where applicable).
5. **Payout Details** — Bank name, account number, account holder name.
6. **Compliance** — Background check consent, terms of service, code of conduct, criminal record declaration.

You can **save progress** and return later. Use **Submit for Review** when all required fields are complete.

### 4.3 After Approval

| Task | Where |
|------|-------|
| Create meals | Chef Dashboard → Meals → Create Meal |
| Manage orders | Chef Dashboard → Orders |
| Accept / reject orders | Order detail page |
| Update status | Preparing → Ready |
| Assign traveler | When order is ready |
| View earnings | Chef Dashboard → Earnings |
| Invoices | Billing / Invoices section |

### 4.4 Tips for Chefs

- Keep meal photos clear and accurate.
- Update availability when you are not cooking.
- Respond to orders promptly.
- Maintain kitchen hygiene standards (see System Guidelines).

---

## 5. Traveler / Dropper Guide

### 5.1 Registration & Approval

1. Register as **Traveler** (email or social sign-in with “Become a Traveler”).
2. Complete the **Traveler Verification** form at `/verify`.
3. Submit identity, address, vehicle, and insurance documents.
4. Wait for admin approval.

### 5.2 Verification Requirements

- Identity: DOB, NIDA ID, nationality, gender, selfie.
- Address: Street, city, district, address type, proof of address.
- Vehicle: Type, license number, vehicle photo, proof of ownership, insurance.
- Banking: Payout account details.
- Compliance: Consent forms and declarations.

### 5.3 After Approval

| Task | Where |
|------|-------|
| Go online / offline | Traveler Dashboard |
| View available deliveries | Deliveries list |
| Accept delivery | Delivery detail |
| Update status | Picked up → Delivered |
| View earnings | Earnings section |

---

## 6. Admin Guide (Summary)

Admins sign in with **email and password only**. Optional **two-factor authentication (2FA)** may be enabled.

| Area | Purpose |
|------|---------|
| Users | Approve, reject, suspend, export users |
| Verifications | Review partner documents |
| Orders | Monitor and manage all orders |
| Finance | Payments and refunds |
| Logistics | Delivery overview |
| Disputes | Resolve customer/partner disputes |
| Analytics | Platform statistics |
| Config & Zones | System settings and delivery zones |

---

## 7. Account & Security

### 7.1 Password

- Required for email-registered accounts (minimum 8 characters).
- Use **Forgot Password** on the login page if needed.
- **Not available** for Google/Facebook-only accounts.

### 7.2 Phone Verification

- Required for all social sign-ins.
- OTP codes expire after **10 minutes**.

### 7.3 Profile

- Edit your **name** and **profile picture** from Dashboard → Profile.
- Email changes for social accounts are managed through your Google/Facebook profile.

### 7.4 Logout

Click **Logout** from the dashboard menu to end your session.

---

## 8. Payments & Invoices

- Mobile money payments (M-Pesa, Tigo, Airtel) are initiated at checkout.
- **Cash on Delivery** is paid to the traveler at delivery.
- View and download **invoices** from **My Orders** or the **Invoices** section after payment.

---

## 9. Help & Support

| Issue | Action |
|-------|--------|
| Cannot sign in (social) | Use Google/Facebook buttons; do not use password |
| Chef/Traveler pending | Complete verification form; contact support if waiting long |
| Order problem | Check order status; contact support with order number |
| Payment issue | Note payment reference; email support |
| Account suspended | Email support@onestopfood.co.tz |

**Support email:** support@onestopfood.co.tz  
**Phone:** +255 651 490 677

---

## 10. Quick Reference — URLs

| Page | Path |
|------|------|
| Home | `/` |
| Login | `/login` |
| Register | `/register` |
| Complete social sign-up | `/auth/complete-signup` |
| Dashboard | `/dashboard` |
| Verification (Chef/Traveler) | `/verify` |
| Browse meals | `/meals` |
| Cart | `/cart` |
| User Manual (online) | `/docs/user-manual` |
| System Guidelines (online) | `/docs/guidelines` |

---

*© One Stop Food. All rights reserved.*

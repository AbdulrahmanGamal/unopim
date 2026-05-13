# Test Cases: Multi-Channel Product Syndication

> **Version:** 1.0  
> **Feature:** EPIC-001 Unified Multi-Channel Product Syndication  
> **Last Updated:** 2026-02-23  
> **Status:** Ready for UAT

---

## Overview

This document provides test cases for business users to validate the Multi-Channel Product Syndication feature. Each test case includes steps, expected results, and acceptance criteria.

---

## Prerequisites

Before testing, ensure:
- [ ] Admin user account with `channel_connector.connectors.*` permissions
- [ ] At least one product created in UnoPim with SKU, name, price, and description
- [ ] Valid credentials for at least one channel (Shopify, Salla, etc.)
- [ ] Field mappings configured for the connector

---

## Test Suite 1: Connector Management

### TC-1.1: Create New Connector

| Field | Value |
|-------|-------|
| **ID** | TC-1.1 |
| **Priority** | Critical |
| **Module** | Channel Connector |
| **Feature** | Connector CRUD |

**Preconditions:**
- User logged in as admin
- User has `channel_connector.connectors.create` permission

**Test Steps:**
1. Navigate to `Admin > Integrations > Channel Connectors`
2. Click "Create Connector" button
3. Fill in the form:
   - Code: `test-shopify-001` (lowercase, hyphens allowed)
   - Name: `Test Shopify Store`
   - Channel Type: `shopify`
   - Credentials: Enter shop URL and access token
4. Click "Save"

**Expected Result:**
- [ ] Connector created successfully
- [ ] Success message displayed
- [ ] Connector appears in the list
- [ ] Status shows "disconnected" initially

**Acceptance Criteria:**
- Connector code must be unique per tenant
- Code validation: only lowercase letters, numbers, hyphens, underscores
- Credentials stored encrypted in database

---

### TC-1.2: Edit Connector

| Field | Value |
|-------|-------|
| **ID** | TC-1.2 |
| **Priority** | High |
| **Module** | Channel Connector |

**Test Steps:**
1. Navigate to connector list
2. Click "Edit" on an existing connector
3. Update the name to `Updated Store Name`
4. Click "Save"

**Expected Result:**
- [ ] Changes saved successfully
- [ ] Updated name displayed in list

---

### TC-1.3: Delete Connector

| Field | Value |
|-------|-------|
| **ID** | TC-1.3 |
| **Priority** | High |
| **Module** | Channel Connector |

**Test Steps:**
1. Create a test connector (if not exists)
2. Click "Delete" on the connector
3. Confirm deletion

**Expected Result:**
- [ ] Connector removed from list
- [ ] Associated mappings marked as orphaned or deleted
- [ ] Success message displayed

---

## Test Suite 2: Connection Testing

### TC-2.1: Test Connection - Valid Credentials

| Field | Value |
|-------|-------|
| **ID** | TC-2.1 |
| **Priority** | Critical |
| **Module** | Connection Test |

**Preconditions:**
- Valid Shopify/Salla credentials configured

**Test Steps:**
1. Navigate to connector edit page
2. Enter valid credentials
3. Click "Test Connection" button

**Expected Result:**
- [ ] Connection successful message displayed
- [ ] Store name shown (e.g., "Connected to: My Store")
- [ ] Product count displayed (if available)
- [ ] Connector status changes to "connected"

---

### TC-2.2: Test Connection - Invalid Credentials

| Field | Value |
|-------|-------|
| **ID** | TC-2.2 |
| **Priority** | High |
| **Module** | Connection Test |

**Test Steps:**
1. Navigate to connector edit page
2. Enter invalid credentials (wrong token)
3. Click "Test Connection"

**Expected Result:**
- [ ] Error message displayed
- [ ] Connector status remains "disconnected" or shows "error"
- [ ] Error details logged

---

## Test Suite 3: Salla OAuth2 Flow

### TC-3.1: Salla OAuth2 Authorization

| Field | Value |
|-------|-------|
| **ID** | TC-3.1 |
| **Priority** | Critical |
| **Module** | Salla Integration |

**Preconditions:**
- Salla connector created with Client ID and Client Secret
- Valid Salla partner account

**Test Steps:**
1. Navigate to Salla connector edit page
2. Enter Client ID and Client Secret
3. Click "Connect with Salla" button
4. Authorize the app on Salla's OAuth page
5. Wait for redirect back to UnoPim

**Expected Result:**
- [ ] Redirected to Salla authorization page
- [ ] After authorization, redirected back to UnoPim
- [ ] Success message displayed
- [ ] Connector status shows "connected"
- [ ] Access token stored (encrypted)

---

### TC-3.2: Salla Token Refresh

| Field | Value |
|-------|-------|
| **ID** | TC-3.2 |
| **Priority** | Medium |
| **Module** | Salla Integration |

**Test Steps:**
1. Connect Salla connector via OAuth
2. Wait for token to expire (or manually expire in database)
3. Trigger a sync operation

**Expected Result:**
- [ ] Token automatically refreshed using refresh_token
- [ ] Sync operation completes successfully
- [ ] New access_token stored

---

## Test Suite 4: Field Mapping

### TC-4.1: Configure Field Mappings

| Field | Value |
|-------|-------|
| **ID** | TC-4.1 |
| **Priority** | Critical |
| **Module** | Field Mapping |

**Preconditions:**
- Connector created and connected

**Test Steps:**
1. Navigate to connector's "Field Mappings" tab
2. Add mappings:
   - UnoPim `name` → Channel `title`
   - UnoPim `price` → Channel `price`
   - UnoPim `description` → Channel `descriptionHtml`
3. Set direction to "Export" or "Both"
4. Save mappings

**Expected Result:**
- [ ] Mappings saved successfully
- [ ] Available channel fields displayed for reference
- [ ] Mapping preview shows sample data

---

### TC-4.2: Locale Mapping

| Field | Value |
|-------|-------|
| **ID** | TC-4.2 |
| **Priority** | High |
| **Module** | Field Mapping |

**Test Steps:**
1. Edit a translatable field mapping (e.g., `name` → `title`)
2. Configure locale mapping:
   - UnoPim `en_US` → Channel `en`
   - UnoPim `ar_AE` → Channel `ar`
3. Save mapping

**Expected Result:**
- [ ] Locale mappings saved
- [ ] During sync, correct locale values sent to channel

---

## Test Suite 5: Product Sync

### TC-5.1: Full Sync - Single Product

| Field | Value |
|-------|-------|
| **ID** | TC-5.1 |
| **Priority** | Critical |
| **Module** | Product Sync |

**Preconditions:**
- Connector connected
- Field mappings configured
- At least one product with all required fields

**Test Steps:**
1. Navigate to connector's "Sync" tab
2. Select sync type: "Single"
3. Enter product SKU
4. Click "Start Sync"
5. Wait for sync completion

**Expected Result:**
- [ ] Sync job created with status "pending"
- [ ] Job status changes to "running"
- [ ] Job status changes to "completed"
- [ ] Product visible on channel (check Shopify/Salla admin)
- [ ] External ID stored in product_channel_mappings table
- [ ] Sync status shows "synced"

---

### TC-5.2: Full Sync - All Products

| Field | Value |
|-------|-------|
| **ID** | TC-5.2 |
| **Priority** | Critical |
| **Module** | Product Sync |

**Test Steps:**
1. Navigate to connector's "Sync" tab
2. Select sync type: "Full"
3. Click "Start Sync"
4. Monitor progress

**Expected Result:**
- [ ] All products queued for sync
- [ ] Progress indicator shows synced/failed counts
- [ ] Products visible on channel
- [ ] Sync job shows completion summary

---

### TC-5.3: Incremental Sync

| Field | Value |
|-------|-------|
| **ID** | TC-5.3 |
| **Priority** | High |
| **Module** | Product Sync |

**Preconditions:**
- At least one full sync completed
- Product updated in UnoPim after last sync

**Test Steps:**
1. Update a product's name in UnoPim
2. Navigate to connector's "Sync" tab
3. Select sync type: "Incremental"
4. Click "Start Sync"

**Expected Result:**
- [ ] Only products updated since last sync are synced
- [ ] Updated product reflects changes on channel
- [ ] Unchanged products skipped (check logs)

---

### TC-5.4: Sync Preview

| Field | Value |
|-------|-------|
| **ID** | TC-5.4 |
| **Priority** | Medium |
| **Module** | Product Sync |

**Test Steps:**
1. Navigate to connector's "Sync" tab
2. Click "Preview" button
3. Review preview data

**Expected Result:**
- [ ] Preview shows sample products with mapped values
- [ ] Data hash displayed for each product
- [ ] Total available products count shown

---

### TC-5.5: Sync - Shopify Variant Fields

| Field | Value |
|-------|-------|
| **ID** | TC-5.5 |
| **Priority** | Critical |
| **Module** | Product Sync |

**Test Steps:**
1. Create product with:
   - SKU: `TEST-SKU-001`
   - Price: `99.99`
   - Barcode: `123456789012`
   - Weight: `1.5`
2. Map these fields to Shopify
3. Sync to Shopify
4. Check product on Shopify admin

**Expected Result:**
- [ ] Product created on Shopify
- [ ] Variant has correct price: $99.99
- [ ] Variant has correct SKU: TEST-SKU-001
- [ ] Variant has correct barcode: 123456789012
- [ ] Variant has correct weight: 1.5

---

## Test Suite 6: Sync Dashboard

### TC-6.1: View Sync Dashboard

| Field | Value |
|-------|-------|
| **ID** | TC-6.1 |
| **Priority** | High |
| **Module** | Sync Dashboard |

**Test Steps:**
1. Navigate to `Admin > Integrations > Channel Connectors > Dashboard`
2. Review dashboard

**Expected Result:**
- [ ] All connectors listed with status
- [ ] Recent sync jobs displayed
- [ ] Success/failure metrics visible
- [ ] Last sync timestamp shown

---

### TC-6.2: Retry Failed Sync

| Field | Value |
|-------|-------|
| **ID** | TC-6.2 |
| **Priority** | High |
| **Module** | Sync Dashboard |

**Preconditions:**
- At least one failed sync job

**Test Steps:**
1. Navigate to sync dashboard
2. Find a failed sync job
3. Click "Retry" button

**Expected Result:**
- [ ] New sync job created
- [ ] Only failed products retried
- [ ] Original job marked as "retrying"

---

## Test Suite 7: Conflict Resolution

### TC-7.1: Detect Conflict (Both Modified)

| Field | Value |
|-------|-------|
| **ID** | TC-7.1 |
| **Priority** | High |
| **Module** | Conflict Resolution |

**Preconditions:**
- Product synced to channel
- Product modified in UnoPim AND on channel (simultaneously)

**Test Steps:**
1. Modify product price in UnoPim
2. Modify same product price directly on channel (different value)
3. Trigger sync from UnoPim
4. Navigate to "Conflicts" tab

**Expected Result:**
- [ ] Conflict detected and recorded
- [ ] Conflict appears in conflicts list
- [ ] Conflict type: "both_modified"
- [ ] Conflicting fields shown with both values

---

### TC-7.2: Resolve Conflict - PIM Wins

| Field | Value |
|-------|-------|
| **ID** | TC-7.2 |
| **Priority** | High |
| **Module** | Conflict Resolution |

**Test Steps:**
1. Navigate to conflict detail page
2. Select resolution: "PIM Wins"
3. Click "Resolve"
4. Check product on channel

**Expected Result:**
- [ ] Conflict marked as resolved
- [ ] PIM value pushed to channel
- [ ] Product mapping updated with new hash

---

### TC-7.3: Resolve Conflict - Channel Wins

| Field | Value |
|-------|-------|
| **ID** | TC-7.3 |
| **Priority** | High |
| **Module** | Conflict Resolution |

**Test Steps:**
1. Navigate to conflict detail page
2. Select resolution: "Channel Wins"
3. Click "Resolve"
4. Check product in UnoPim

**Expected Result:**
- [ ] Conflict marked as resolved
- [ ] Channel value pulled to UnoPim
- [ ] Product values updated in UnoPim

---

### TC-7.4: Auto-Resolve - PIM Always Wins

| Field | Value |
|-------|-------|
| **ID** | TC-7.4 |
| **Priority** | Medium |
| **Module** | Conflict Resolution |

**Test Steps:**
1. Edit connector settings
2. Set conflict strategy to "pim_always_wins"
3. Create a conflict scenario
4. Trigger sync

**Expected Result:**
- [ ] No conflict record created
- [ ] PIM value automatically pushed to channel
- [ ] Sync continues without manual intervention

---

## Test Suite 8: Webhooks

### TC-8.1: Configure Webhooks

| Field | Value |
|-------|-------|
| **ID** | TC-8.1 |
| **Priority** | High |
| **Module** | Webhooks |

**Test Steps:**
1. Navigate to connector's "Webhooks" tab
2. Select events: `product.created`, `product.updated`, `product.deleted`
3. Set inbound strategy: "Flag for Review"
4. Save settings

**Expected Result:**
- [ ] Webhook token generated automatically
- [ ] Callback URL displayed
- [ ] Webhooks registered with channel (check channel admin)

---

### TC-8.2: Receive Webhook - Product Updated

| Field | Value |
|-------|-------|
| **ID** | TC-8.2 |
| **Priority** | High |
| **Module** | Webhooks |

**Preconditions:**
- Webhooks configured with "Flag for Review" strategy
- Product exists in both UnoPim and channel

**Test Steps:**
1. Update product directly on channel
2. Wait for webhook to be received (check logs)
3. Navigate to "Conflicts" tab

**Expected Result:**
- [ ] Webhook received within 2 seconds
- [ ] HMAC signature verified
- [ ] Conflict/flag created for review
- [ ] Event logged with payload

---

### TC-8.3: Webhook - Auto Update Strategy

| Field | Value |
|-------|-------|
| **ID** | TC-8.3 |
| **Priority** | High |
| **Module** | Webhooks |

**Preconditions:**
- Webhooks configured with "Auto Update" strategy

**Test Steps:**
1. Update product on channel
2. Wait for webhook processing
3. Check product in UnoPim

**Expected Result:**
- [ ] Product automatically updated in UnoPim
- [ ] No conflict created
- [ ] Sync status remains "synced"

---

### TC-8.4: Webhook Security - Invalid Signature

| Field | Value |
|-------|-------|
| **ID** | TC-8.4 |
| **Priority** | Critical |
| **Module** | Webhooks |

**Test Steps:**
1. Send a webhook request with invalid signature (use Postman/curl)
2. Check response

**Expected Result:**
- [ ] Request rejected with 401 Unauthorized
- [ ] Error logged
- [ ] No processing occurs

---

## Test Suite 9: Order Management

### TC-9.1: View Orders List

| Field | Value |
|-------|-------|
| **ID** | TC-9.1 |
| **Priority** | High |
| **Module** | Order Management |

**Test Steps:**
1. Navigate to `Admin > Orders`
2. Review order list

**Expected Result:**
- [ ] Orders from all channels displayed
- [ ] Order columns: ID, Channel, Customer, Total, Status, Date
- [ ] Filtering and sorting available

---

### TC-9.2: Update Order Status

| Field | Value |
|-------|-------|
| **ID** | TC-9.2 |
| **Priority** | High |
| **Module** | Order Management |

**Test Steps:**
1. Navigate to order detail page
2. Change status from "pending" to "processing"
3. Save changes

**Expected Result:**
- [ ] Status updated successfully
- [ ] Status history logged
- [ ] Event dispatched

---

### TC-9.3: Order Webhook Ingestion

| Field | Value |
|-------|-------|
| **ID** | TC-9.3 |
| **Priority** | Critical |
| **Module** | Order Management |

**Test Steps:**
1. Create an order on channel (Shopify/Salla)
2. Wait for webhook to be received
3. Check orders in UnoPim

**Expected Result:**
- [ ] Order created in UnoPim automatically
- [ ] Order details match channel order
- [ ] Line items included

---

## Test Suite 10: Profitability Analysis

### TC-10.1: View Profitability Dashboard

| Field | Value |
|-------|-------|
| **ID** | TC-10.1 |
| **Priority** | Medium |
| **Module** | Profitability |

**Test Steps:**
1. Navigate to `Admin > Orders > Profitability`
2. Select date range
3. Review dashboard

**Expected Result:**
- [ ] Revenue displayed
- [ ] Cost calculated from product costs
- [ ] Profit and margin percentage shown
- [ ] Data filterable by channel

---

### TC-10.2: Profitability by Product

| Field | Value |
|-------|-------|
| **ID** | TC-10.2 |
| **Priority** | Medium |
| **Module** | Profitability |

**Test Steps:**
1. Navigate to profitability dashboard
2. Switch to "By Product" view

**Expected Result:**
- [ ] Products listed with individual profitability
- [ ] Quantity sold per product
- [ ] Average margin per product

---

## Test Suite 11: Rate Limiting

### TC-11.1: Rate Limit Tracking

| Field | Value |
|-------|-------|
| **ID** | TC-11.1 |
| **Priority** | Medium |
| **Module** | Rate Limiting |

**Test Steps:**
1. Navigate to `Admin > Integrations > Rate Limits`
2. Review rate limit metrics

**Expected Result:**
- [ ] Current rate limit usage per connector
- [ ] Alerts for approaching limits
- [ ] Historical rate limit data

---

## Test Suite 12: Permissions & ACL

### TC-12.1: Connector View Permission

| Field | Value |
|-------|-------|
| **ID** | TC-12.1 |
| **Priority** | High |
| **Module** | ACL |

**Test Steps:**
1. Create role without `channel_connector.connectors.view` permission
2. Assign role to test user
3. Login as test user
4. Try to access connector list

**Expected Result:**
- [ ] Access denied (403 Forbidden)
- [ ] Appropriate error message displayed

---

### TC-12.2: Sync Create Permission

| Field | Value |
|-------|-------|
| **ID** | TC-12.2 |
| **Priority** | High |
| **Module** | ACL |

**Test Steps:**
1. Create role with view but without `channel_connector.sync.create` permission
2. Try to trigger sync

**Expected Result:**
- [ ] Sync button not visible or disabled
- [ ] If attempted via API, 403 returned

---

## Test Suite 13: Error Handling

### TC-13.1: Sync Job Failure

| Field | Value |
|-------|-------|
| **ID** | TC-13.1 |
| **Priority** | High |
| **Module** | Error Handling |

**Test Steps:**
1. Disconnect channel (revoke token on channel side)
2. Trigger sync

**Expected Result:**
- [ ] Sync job marked as failed
- [ ] Error details captured in error_summary
- [ ] Event `SyncFailed` dispatched
- [ ] Notification sent to admin

---

### TC-13.2: Partial Sync Failure Recovery

| Field | Value |
|-------|-------|
| **ID** | TC-13.2 |
| **Priority** | High |
| **Module** | Error Handling |

**Test Steps:**
1. Have some products with invalid data (missing required field)
2. Trigger full sync
3. Review results

**Expected Result:**
- [ ] Valid products synced successfully
- [ ] Invalid products marked as failed with errors
- [ ] Sync job marked as "completed" (not failed) if any succeeded
- [ ] Retry only processes failed products

---

## Test Suite 14: Multi-Tenant Isolation

### TC-14.1: Tenant Data Isolation

| Field | Value |
|-------|-------|
| **ID** | TC-14.1 |
| **Priority** | Critical |
| **Module** | Multi-Tenant |

**Test Steps:**
1. Login as Tenant A admin
2. Create connector and products
3. Login as Tenant B admin
4. Try to access Tenant A's connectors

**Expected Result:**
- [ ] Tenant B cannot see Tenant A's data
- [ ] API queries filtered by tenant_id
- [ ] Direct URL access returns 404

---

## Test Summary Checklist

| Suite | Test Cases | Priority | Status |
|-------|------------|----------|--------|
| 1. Connector Management | TC-1.1 to TC-1.3 | Critical/High | [ ] Pass [ ] Fail |
| 2. Connection Testing | TC-2.1 to TC-2.2 | Critical/High | [ ] Pass [ ] Fail |
| 3. Salla OAuth2 | TC-3.1 to TC-3.2 | Critical/Medium | [ ] Pass [ ] Fail |
| 4. Field Mapping | TC-4.1 to TC-4.2 | Critical/High | [ ] Pass [ ] Fail |
| 5. Product Sync | TC-5.1 to TC-5.5 | Critical | [ ] Pass [ ] Fail |
| 6. Sync Dashboard | TC-6.1 to TC-6.2 | High | [ ] Pass [ ] Fail |
| 7. Conflict Resolution | TC-7.1 to TC-7.4 | High/Medium | [ ] Pass [ ] Fail |
| 8. Webhooks | TC-8.1 to TC-8.4 | Critical/High | [ ] Pass [ ] Fail |
| 9. Order Management | TC-9.1 to TC-9.3 | Critical/High | [ ] Pass [ ] Fail |
| 10. Profitability | TC-10.1 to TC-10.2 | Medium | [ ] Pass [ ] Fail |
| 11. Rate Limiting | TC-11.1 | Medium | [ ] Pass [ ] Fail |
| 12. Permissions | TC-12.1 to TC-12.2 | High | [ ] Pass [ ] Fail |
| 13. Error Handling | TC-13.1 to TC-13.2 | High | [ ] Pass [ ] Fail |
| 14. Multi-Tenant | TC-14.1 | Critical | [ ] Pass [ ] Fail |

---

## Sign-Off

| Role | Name | Date | Signature |
|------|------|------|-----------|
| QA Lead | | | |
| Business Analyst | | | |
| Product Owner | | | |
| Tech Lead | | | |

---

## Revision History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2026-02-23 | System | Initial test case document |

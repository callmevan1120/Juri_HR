import { expect, test } from '@playwright/test';

const adminEmail = process.env.E2E_ADMIN_EMAIL;
const adminPassword = process.env.E2E_ADMIN_PASSWORD ?? 'password';
const userEmail = process.env.E2E_USER_EMAIL;
const userPassword = process.env.E2E_USER_PASSWORD ?? 'password';

async function login(page, email: string, password: string) {
  await page.goto('/login');
  await page.getByLabel(/email/i).fill(email);
  await page.getByLabel(/password/i).fill(password);
  await page.getByRole('button', { name: /log in|login|masuk/i }).click();
  await expect(page).not.toHaveURL(/\/login$/);
}

test('public login page renders', async ({ page }) => {
  await page.goto('/login');
  await expect(page.getByLabel(/email/i)).toBeVisible();
  await expect(page.getByLabel(/password/i)).toBeVisible();
});

test('admin smoke covers dashboard employees attendance HR payroll reports', async ({ page }) => {
  test.skip(!adminEmail, 'Set E2E_ADMIN_EMAIL and E2E_ADMIN_PASSWORD to run authenticated admin smoke.');

  await login(page, adminEmail!, adminPassword);

  for (const path of [
    '/admin/dashboard',
    '/admin/employees',
    '/admin/attendances',
    '/admin/hr-checklists',
    '/admin/reimbursements',
    '/admin/payrolls',
    '/admin/reports',
  ]) {
    await page.goto(path);
    await expect(page.locator('body')).toBeVisible();
    await expect(page.locator('body')).not.toContainText(/server error|exception|stack trace/i);
  }
});

test('user smoke covers attendance leave reimbursement payslip documents approvals', async ({ page }) => {
  test.skip(!userEmail, 'Set E2E_USER_EMAIL and E2E_USER_PASSWORD to run authenticated user smoke.');

  await login(page, userEmail!, userPassword);

  for (const path of [
    '/home',
    '/scan',
    '/apply-leave',
    '/reimbursement',
    '/payroll',
    '/document-requests',
    '/approvals',
  ]) {
    await page.goto(path);
    await expect(page.locator('body')).toBeVisible();
    await expect(page.locator('body')).not.toContainText(/server error|exception|stack trace/i);
  }
});

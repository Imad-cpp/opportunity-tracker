import { expect, test } from "@playwright/test";

function localDateTime(daysFromNow: number): string {
  const value = new Date(Date.now() + daysFromNow * 24 * 60 * 60 * 1000);
  const pad = (number: number) => String(number).padStart(2, "0");
  return `${value.getFullYear()}-${pad(value.getMonth() + 1)}-${pad(value.getDate())}T${pad(value.getHours())}:${pad(value.getMinutes())}`;
}

function localDate(daysFromNow: number): string {
  return localDateTime(daysFromNow).slice(0, 10);
}

test("register through delete works through the browser", async ({ page }) => {
  const suffix = Date.now();
  const email = `e2e-${suffix}@example.test`;
  const password = "Synthetic-Password-123!";
  const title = `E2E Platform Internship ${suffix}`;
  const notes = '<strong>literal note</strong> <script>window.__unsafe = true</script>';

  await page.goto("http://localhost:3000/");
  await expect(page.getByRole("heading", { name: "Sign in to your workspace" })).toBeVisible();

  await page.getByRole("tab", { name: "Create account" }).click();
  await page.getByLabel("Name").fill("E2E Student");
  await page.getByLabel("Email").fill(email);
  await page.getByLabel("Password").fill(password);
  await page.getByRole("button", { name: "Create account" }).click();

  await expect(page.getByRole("navigation", { name: "Workspace" })).toBeVisible();
  await expect(page.getByRole("heading", { name: "Know what needs your attention next." })).toBeVisible();

  await page.getByRole("button", { name: "Sign out" }).click();
  await expect(page.getByRole("heading", { name: "Sign in to your workspace" })).toBeVisible();
  await page.getByLabel("Email").fill(email);
  await page.getByLabel("Password").fill(password);
  await page.getByRole("button", { name: "Sign in" }).click();

  await page.getByRole("button", { name: "Opportunities" }).click();
  await expect(page.getByRole("heading", { name: "Opportunities" })).toBeVisible();
  await page.getByRole("button", { name: "Add opportunity" }).click();

  await page.getByLabel("Type").selectOption("INTERNSHIP");
  await page.getByLabel("Priority").selectOption("HIGH");
  await page.getByLabel("Title").fill(title);
  await page.getByLabel("Organization").fill("Synthetic Research Lab");
  await page.getByLabel("Source URL").fill("https://example.test/opportunity");
  await page.getByLabel("Location").fill("Remote");
  await page.getByLabel("Precision").selectOption("DATE");
  await page.getByLabel("Date", { exact: true }).fill(localDate(4));
  await page.getByLabel("Next action", { exact: true }).fill("Finish portfolio review");
  await page.getByLabel("Next action date").fill(localDateTime(2));
  await page.getByLabel("Notes").fill(notes);
  await page.getByRole("button", { name: "Add opportunity" }).click();

  await expect(page.getByRole("heading", { name: title })).toBeVisible();
  await expect(page.locator(".plain-notes")).toHaveText(notes);
  await expect(page.locator(".plain-notes script")).toHaveCount(0);
  await expect(page.getByText("Finish portfolio review", { exact: true })).toBeVisible();

  await page.getByRole("button", { name: "Edit" }).click();
  await page.getByLabel("Location").fill("Hybrid");
  await page.getByRole("button", { name: "Save changes" }).click();
  await expect(page.getByText("Hybrid", { exact: true })).toBeVisible();

  await page
    .getByRole("region", { name: "Application status" })
    .getByRole("combobox", { name: "Status" })
    .selectOption("APPLIED");
  await page.getByRole("button", { name: "Update status" }).click();
  await expect(page.locator(".status-chip").filter({ hasText: "Applied" })).toBeVisible();

  await page.getByRole("button", { name: "Opportunities" }).click();
  await page.getByPlaceholder("Search title or organization").fill(title);
  await page.getByRole("button", { name: "Apply" }).click();
  await expect(page.getByText(title, { exact: true })).toBeVisible();

  await page.getByRole("button", { name: "Dashboard" }).click();
  await expect(page.getByRole("heading", { name: "Next actions" })).toBeVisible();
  await expect(page.getByText(title, { exact: true })).toBeVisible();
  await expect(page.getByText("Finish portfolio review", { exact: true })).toBeVisible();

  await page.getByRole("button", { name: "Opportunities" }).click();
  await page.getByRole("button", { name: new RegExp(title) }).click();
  await page.getByRole("button", { name: "Archive opportunity" }).click();
  await expect(page.locator(".status-chip--muted")).toHaveText("Archived");

  await page.getByRole("button", { name: "Opportunities" }).click();
  await expect(page.getByText(title, { exact: true })).toHaveCount(0);
  await page.getByLabel("Archived").check();
  await page.getByRole("button", { name: "Apply" }).click();
  await expect(page.getByText(title, { exact: true })).toBeVisible();
  await page.getByRole("button", { name: new RegExp(title) }).click();
  await page.getByRole("button", { name: "Restore opportunity" }).click();
  await expect(page.locator(".status-chip--muted")).toHaveCount(0);

  await page.getByRole("button", { name: "Delete permanently" }).click();
  await expect(page.getByText("This permanently removes the opportunity and its history.")).toBeVisible();
  await page.getByRole("button", { name: "Confirm delete" }).click();
  await expect(page.getByRole("heading", { name: "Opportunities" })).toBeVisible();

  await page.getByPlaceholder("Search title or organization").fill(title);
  await page.getByRole("button", { name: "Apply" }).click();
  await expect(page.getByText(title, { exact: true })).toHaveCount(0);
  await expect(page.getByRole("heading", { name: "Nothing matches these filters." })).toBeVisible();
});

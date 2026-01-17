<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DevKnowledgeBaseSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment(['local', 'testing'])) {
            return;
        }

        $authorId = $this->findUserIdByEmail('it@example.com')
            ?: $this->findUserIdByEmail('admin@example.com');

        if (!$authorId) {
            return;
        }

        $categories = [
            ['name' => 'Account & Access', 'description' => 'Passwords, MFA, accounts, and permissions.'],
            ['name' => 'Email & Calendar', 'description' => 'Outlook, mail apps, and calendar sync.'],
            ['name' => 'VPN & Network', 'description' => 'Remote access, Wi‑Fi, and connectivity.'],
            ['name' => 'Devices & Printing', 'description' => 'Printers, peripherals, and hardware.'],
            ['name' => 'Software', 'description' => 'Installations, licensing, and updates.'],
            ['name' => 'Security', 'description' => 'MFA, phishing, and security tips.'],
        ];

        $categoryIdByName = $this->ensureCategories($categories);
        if (count($categoryIdByName) === 0) {
            return;
        }

        $articles = [
            [
                'category' => 'Account & Access',
                'title' => 'Reset your account password (self-service portal)',
                'slug' => 'reset-your-account-password',
                'summary' => 'Use the self-service portal to reset or unlock your account using MFA.',
                'featured' => 1,
                'content_html' => $this->articleTemplate(
                    appliesTo: ['Staff & Students', 'Windows 10/11, macOS 12+', 'Web browser'],
                    prerequisites: ['MFA set up (Authenticator app or SMS)', 'Access to your registered device'],
                    steps: [
                        ['Open the password portal', 'Go to the Password Portal and choose “Reset password”.', 'info', 'Bookmark the portal for future use.'],
                        ['Verify with MFA', 'Approve the login request on your authenticator app or enter the SMS code.', 'tip', 'If you changed phones, update your MFA device first.'],
                        ['Set a new password', 'Create a new password (8+ characters) and avoid reusing recent passwords.', 'warn', 'Avoid reusing recent passwords.'],
                        ['Update your devices', 'Update saved passwords in Outlook, browser, phone, and other apps.', null, null],
                    ],
                    troubleshooting: [
                        ['I no longer have my MFA device', 'Use your backup method (SMS) if available, otherwise create a ticket for identity verification.'],
                        ['The portal says “account locked”', 'Wait 15 minutes and retry. If still locked, use “Unlock account” or contact support.'],
                    ],
                    related: [
                        ['Update MFA device', route('knowledge')],
                    ]
                ),
            ],
            [
                'category' => 'VPN & Network',
                'title' => 'VPN: Connect from home (Windows & macOS)',
                'slug' => 'vpn-connect-from-home',
                'summary' => 'Install the VPN client and connect securely to internal resources.',
                'featured' => 1,
                'content_html' => $this->articleTemplate(
                    appliesTo: ['Staff', 'Windows 10/11, macOS 12+', 'Home networks'],
                    prerequisites: ['Valid staff account', 'MFA enabled'],
                    steps: [
                        ['Install the VPN client', 'Download and install the approved VPN client for your OS.', 'info', 'Restart after installation if prompted.'],
                        ['Sign in', 'Enter your credentials and approve MFA.', null, null],
                        ['Connect', 'Choose the default gateway and click Connect.', 'tip', 'If you get a timeout, try switching networks or rebooting your router.'],
                    ],
                    troubleshooting: [
                        ['Credentials rejected', 'Confirm your password works on the portal, then retry VPN sign-in.'],
                        ['Connection timeout', 'Check your internet connection and temporarily disable other VPNs.'],
                    ],
                    related: []
                ),
            ],
            [
                'category' => 'Email & Calendar',
                'title' => 'Outlook: Fix “Disconnected” status',
                'slug' => 'outlook-fix-disconnected',
                'summary' => 'Troubleshoot connectivity and profile issues causing Outlook to go offline.',
                'featured' => 1,
                'content_html' => $this->articleTemplate(
                    appliesTo: ['Staff & Students', 'Outlook 2019/365', 'Windows/macOS'],
                    prerequisites: ['Internet connection'],
                    steps: [
                        ['Check offline mode', 'Make sure Outlook is not set to “Work Offline”.', null, null],
                        ['Restart Outlook', 'Close and reopen Outlook to refresh the session.', null, null],
                        ['Rebuild profile', 'If the issue persists, create a new mail profile and re-add your account.', 'warn', 'This may reset some local Outlook settings.'],
                    ],
                    troubleshooting: [
                        ['Still disconnected', 'Create a ticket and include screenshots and any error text.'],
                    ],
                    related: []
                ),
            ],
        ];

        foreach ($articles as $a) {
            $categoryId = $categoryIdByName[$a['category']] ?? null;
            if (!$categoryId) {
                continue;
            }

            if ($this->articleExists($a['slug'])) {
                continue;
            }

            DB::select(
                'EXEC dbo.sp_create_kb_article @category_id=?, @title=?, @slug=?, @summary=?, @content_html=?, @is_featured=?, @is_published=?, @created_by=?',
                [
                    $categoryId,
                    $a['title'],
                    $a['slug'],
                    $a['summary'],
                    $a['content_html'],
                    (int) $a['featured'],
                    1,
                    $authorId,
                ]
            );
        }
    }

    private function ensureCategories(array $categories): array
    {
        $existing = [];
        try {
            foreach (DB::select('EXEC dbo.sp_read_kb_categories') as $row) {
                $existing[(string) $row->name] = (int) $row->category_id;
            }
        } catch (\Throwable) {
            // ignore
        }

        foreach ($categories as $c) {
            $name = (string) $c['name'];
            if (isset($existing[$name])) {
                continue;
            }

            $rows = DB::select('EXEC dbo.sp_create_kb_category @name=?, @description=?', [$name, $c['description']]);
            $id = (int) (($rows[0]->category_id ?? 0) ?: 0);
            if ($id > 0) {
                $existing[$name] = $id;
            }
        }

        return $existing;
    }

    private function articleExists(string $slug): bool
    {
        try {
            $rows = DB::select('EXEC dbo.sp_read_kb_article_by_slug @slug = ?', [$slug]);
            return isset($rows[0]);
        } catch (\Throwable) {
            return false;
        }
    }

    private function findUserIdByEmail(string $email): ?int
    {
        try {
            $rows = DB::select('EXEC dbo.sp_read_user_by_email @email = ?', [$email]);
            $u = $rows[0] ?? null;
            if (!$u) {
                return null;
            }

            return (int) $u->user_id;
        } catch (\Throwable) {
            return null;
        }
    }

    private function articleTemplate(array $appliesTo, array $prerequisites, array $steps, array $troubleshooting, array $related): string
    {
        $appliesHtml = '<ul>' . implode('', array_map(fn ($x) => '<li>'.e($x).'</li>', $appliesTo)) . '</ul>';
        $prereqHtml = '<ul>' . implode('', array_map(fn ($x) => '<li>'.e($x).'</li>', $prerequisites)) . '</ul>';

        $stepsHtml = '';
        foreach ($steps as $idx => $s) {
            [$title, $body, $calloutType, $calloutText] = $s;
            $stepId = 'step-' . ($idx + 1);

            $callout = '';
            if ($calloutType && $calloutText) {
                $icon = match ($calloutType) {
                    'info' => 'bx bx-info-circle',
                    'tip' => 'bx bx-bulb',
                    'warn' => 'bx bx-error-circle',
                    default => 'bx bx-info-circle',
                };
                $callout = '<div class="callout '.$calloutType.'"><i class=\''.$icon.'\'></i> '.e($calloutText).'</div>';
            }

                        $stepsHtml .=
                                '<li id="'.e($stepId).'">'
                                .'<div class="step-head">'
                                .'<span class="step-num">'.($idx + 1).'</span>'
                                .'<h4>'.e($title).'</h4>'
                                .'<button class="copy-link" data-target="#'.e($stepId).'" title="Copy link"><i class=\'bx bx-link\'></i></button>'
                                .'</div>'
                                .'<p>'.e($body).'</p>'
                                .$callout
                                .'</li>';
        }

        $tsHtml = '';
        foreach ($troubleshooting as $t) {
            [$title, $body] = $t;
            $tsHtml .= '<details class="ts-item"><summary><i class=\'bx bx-wrench\'></i> '.e($title).'</summary><div class="ts-body">'.e($body).'</div></details>';
        }

        $relatedHtml = '';
        if (count($related) > 0) {
            $items = '';
            foreach ($related as $r) {
                [$text, $href] = $r;
                $items .= '<li><a href="'.e($href).'">'.e($text).'</a></li>';
            }
            $relatedHtml = '<section class="related"><h2>Related Articles</h2><ul>'.$items.'</ul></section>';
        }

        return <<<HTML
<section class="two-col">
  <article class="info-card">
    <h3>Applies to</h3>
    $appliesHtml
  </article>
  <article class="info-card">
    <h3>Prerequisites</h3>
    $prereqHtml
  </article>
</section>

<section class="steps">
  <h2>Steps</h2>
  <ol class="step-list">
    $stepsHtml
  </ol>
</section>

<section class="troubleshoot">
  <h2>Troubleshooting</h2>
  $tsHtml
</section>

$relatedHtml
HTML;
    }
}

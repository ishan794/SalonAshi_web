<?php

namespace App\Modules\Frontend\Controllers;

use App\Controllers\BaseController;
use App\Modules\Settings\Models\SettingModel;
use App\Modules\Staff\Models\StaffModel;

class PagesController extends BaseController
{
    private SettingModel $s;

    public function __construct()
    {
        $this->s = new SettingModel();
    }

    // ── Static content pages (about / terms / privacy / refund) ──

    public function about()
    {
        return $this->renderStatic(
            'about',
            $this->s->get('page_about_title') ?: lang('Site.about_page.heading'),
            $this->s->get('page_about_content') ?: $this->defaultAbout(),
        );
    }

    public function terms()
    {
        return $this->renderStatic(
            'terms',
            $this->s->get('page_terms_title') ?: lang('Site.pages.terms_heading'),
            $this->s->get('page_terms_content') ?: $this->defaultTerms(),
        );
    }

    public function privacy()
    {
        return $this->renderStatic(
            'privacy',
            $this->s->get('page_privacy_title') ?: lang('Site.pages.privacy_heading'),
            $this->s->get('page_privacy_content') ?: $this->defaultPrivacy(),
        );
    }

    public function refund()
    {
        return $this->renderStatic(
            'refund',
            $this->s->get('page_refund_title') ?: lang('Site.pages.refund_heading'),
            $this->s->get('page_refund_content') ?: $this->defaultRefund(),
        );
    }

    // ── Team ──

    public function team()
    {
        $staff = (new StaffModel())->where('is_active', 1)->orderBy('full_name')->findAll();
        return view('App\Modules\Frontend\Views\layout', [
            'title'   => lang('Site.team_page.heading') . ' — ' . $this->s->get('salon_name', 'SalonCMS'),
            'subview' => 'App\Modules\Frontend\Views\team',
            's'       => $this->s,
            'data'    => compact('staff'),
            'page'    => 'team',
        ]);
    }

    // ── Contact ──

    public function contact()
    {
        return view('App\Modules\Frontend\Views\layout', [
            'title'   => lang('Site.contact_page.heading') . ' — ' . $this->s->get('salon_name', 'SalonCMS'),
            'subview' => 'App\Modules\Frontend\Views\contact',
            's'       => $this->s,
            'data'    => [],
            'page'    => 'contact',
        ]);
    }

    public function contactSubmit()
    {
        $name    = trim((string) $this->request->getPost('name'));
        $email   = trim((string) $this->request->getPost('email'));
        $mobile  = trim((string) $this->request->getPost('mobile'));
        $message = trim((string) $this->request->getPost('message'));

        if ($name === '' || $message === '' || ($email === '' && $mobile === '')) {
            return redirect()->back()->with('flash_error', 'Please fill name, message and at least one contact field.');
        }

        // Attempt to email the message via existing SMTP settings (best-effort).
        $to = $this->s->get('biz_email') ?: $this->s->get('smtp_from_email');
        if ($to) {
            try {
                $mailer = \Config\Services::email();
                $mailer->setFrom($this->s->get('smtp_from_email') ?: $to, $this->s->get('salon_name', 'SalonCMS'));
                $mailer->setReplyTo($email ?: $to, $name);
                $mailer->setTo($to);
                $mailer->setSubject('New contact form message — ' . $name);
                $mailer->setMessage(
                    "Name:    {$name}\n" .
                    "Email:   {$email}\n" .
                    "Mobile:  {$mobile}\n\n" .
                    "Message:\n{$message}\n"
                );
                $mailer->send();
            } catch (\Throwable $e) {
                // Non-fatal — the message is still acknowledged to the visitor.
                log_message('error', 'Contact form send failed: ' . $e->getMessage());
            }
        }

        return redirect()->to('contact')->with('flash_success', lang('Site.contact_page.thanks'));
    }

    // ── Internals ──

    private function renderStatic(string $slug, string $heading, string $content)
    {
        return view('App\Modules\Frontend\Views\layout', [
            'title'   => $heading . ' — ' . $this->s->get('salon_name', 'SalonCMS'),
            'subview' => 'App\Modules\Frontend\Views\page',
            's'       => $this->s,
            'data'    => [
                'heading' => $heading,
                'content' => $content,
                'slug'    => $slug,
            ],
            'page'    => $slug,
        ]);
    }

    // ── Default content (used until admin edits the page in Settings → Pages) ──

    private function defaultAbout(): string
    {
        $name = 'SALON ASHI';
        return <<<HTML
<div class="space-y-16">
    <!-- Intro section -->
    <div data-aos="fade-up">
        <h2 class="text-3xl font-bold text-brand-400 font-display uppercase tracking-widest mb-8 flex items-center gap-4">
            Welcome to {$name}
            <span class="flex-1 h-px bg-brand-500/20"></span>
        </h2>
        <div class="bg-zinc-900/80 border border-brand-500/20 p-6 sm:p-8 shadow-xl">
            <p class="text-xl font-light text-white mb-6">Great hair starts with a good conversation.</p>
            <p class="text-gray-300 font-light leading-relaxed mb-8">{$name} is a unisex salon built around one simple idea — great hair and beauty should be easy to access, easy to book, and always worth your time. We offer everything from a quick trim to a full bridal transformation, all under one roof.</p>
            <a href="/book" class="inline-flex items-center gap-2 bg-brand-500 px-8 py-4 text-sm font-semibold text-zinc-950 hover:bg-white transition-colors font-display uppercase tracking-widest shadow-lg shadow-brand-500/20 no-underline">Book an appointment <i data-lucide="arrow-right" class="size-4"></i></a>
        </div>
    </div>

    <!-- What we stand for -->
    <div data-aos="fade-up">
        <h2 class="text-3xl font-bold text-brand-400 font-display uppercase tracking-widest mb-8 flex items-center gap-4">
            What we stand for
            <span class="flex-1 h-px bg-brand-500/20"></span>
        </h2>
        <p class="text-white mb-8 text-lg font-light">Three things we never compromise on.</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="group bg-zinc-900/80 border border-brand-500/20 p-6 sm:p-8 hover:border-brand-500 transition-all duration-300 shadow-xl relative overflow-hidden">
                <h4 class="text-xl font-bold font-display tracking-widest text-white group-hover:text-brand-400 uppercase mb-4 transition-colors">Quality</h4>
                <p class="text-base font-light text-gray-400 leading-relaxed">We use professional-grade products and stay trained on the latest techniques — because your hair deserves more than a rushed job.</p>
                <div class="absolute inset-0 bg-gradient-to-br from-brand-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
            </div>
            <div class="group bg-zinc-900/80 border border-brand-500/20 p-6 sm:p-8 hover:border-brand-500 transition-all duration-300 shadow-xl relative overflow-hidden">
                <h4 class="text-xl font-bold font-display tracking-widest text-white group-hover:text-brand-400 uppercase mb-4 transition-colors">Comfort</h4>
                <p class="text-base font-light text-gray-400 leading-relaxed">From the moment you walk in, you're in a calm, welcoming space. No rushing, no pressure — just a good experience from start to finish.</p>
                <div class="absolute inset-0 bg-gradient-to-br from-brand-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
            </div>
            <div class="group bg-zinc-900/80 border border-brand-500/20 p-6 sm:p-8 hover:border-brand-500 transition-all duration-300 shadow-xl relative overflow-hidden">
                <h4 class="text-xl font-bold font-display tracking-widest text-white group-hover:text-brand-400 uppercase mb-4 transition-colors">Transparency</h4>
                <p class="text-base font-light text-gray-400 leading-relaxed">Every service shows its price and duration upfront — no hidden charges, no surprises when you reach the till.</p>
                <div class="absolute inset-0 bg-gradient-to-br from-brand-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
            </div>
        </div>
    </div>

    <!-- Meet the team -->
    <div data-aos="fade-up">
        <h2 class="text-3xl font-bold text-brand-400 font-display uppercase tracking-widest mb-8 flex items-center gap-4">
            Meet the team
            <span class="flex-1 h-px bg-brand-500/20"></span>
        </h2>
        <p class="text-white mb-8 text-lg font-light">The people behind your look.</p>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="group bg-zinc-900/80 border border-brand-500/20 p-6 sm:p-8 hover:border-brand-500 transition-all duration-300 shadow-xl flex flex-col justify-between relative overflow-hidden">
                <div class="relative z-10">
                    <div class="size-16 bg-zinc-800 text-brand-500 flex items-center justify-center font-display text-2xl font-bold mb-6">A</div>
                    <h4 class="text-xl font-bold font-display tracking-widest text-white group-hover:text-brand-400 uppercase mb-1 transition-colors">ashi by</h4>
                    <p class="text-sm font-display tracking-widest uppercase text-gray-500 mb-6">Owner</p>
                    <p class="text-base font-light text-gray-400 leading-relaxed mb-6">Ashi leads the salon with years of expertise, ensuring every client receives top-tier service and leaves with a smile.</p>
                </div>
                <div class="absolute inset-0 bg-gradient-to-br from-brand-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
            </div>
            
            <div class="group bg-zinc-900/80 border border-brand-500/20 p-6 sm:p-8 hover:border-brand-500 transition-all duration-300 shadow-xl flex flex-col justify-between relative overflow-hidden">
                <div class="relative z-10">
                    <div class="size-16 bg-zinc-800 text-brand-500 flex items-center justify-center font-display text-2xl font-bold mb-6">M</div>
                    <h4 class="text-xl font-bold font-display tracking-widest text-white group-hover:text-brand-400 uppercase mb-1 transition-colors">M.A.Shehan Rashmitha</h4>
                    <p class="text-sm font-display tracking-widest uppercase text-gray-500 mb-6">Stylist</p>
                    <p class="text-base font-light text-gray-400 leading-relaxed mb-6">Rash is our expert stylist, known for his precision cuts and modern styling techniques tailored to each individual.</p>
                </div>
                <div class="absolute inset-0 bg-gradient-to-br from-brand-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
            </div>

            <div class="group bg-zinc-900/80 border border-brand-500/20 p-6 sm:p-8 hover:border-brand-500 transition-all duration-300 shadow-xl flex flex-col justify-between relative overflow-hidden">
                <div class="relative z-10">
                    <div class="size-16 bg-zinc-800 text-brand-500 flex items-center justify-center font-display text-2xl font-bold mb-6">S</div>
                    <h4 class="text-xl font-bold font-display tracking-widest text-white group-hover:text-brand-400 uppercase mb-1 transition-colors">Sadew</h4>
                    <p class="text-sm font-display tracking-widest uppercase text-gray-500 mb-6">Stylist</p>
                    <p class="text-base font-light text-gray-400 leading-relaxed mb-6">Sadew brings creativity and a relaxed vibe to every appointment. He is a versatile stylist for all hair types.</p>
                </div>
                <div class="absolute inset-0 bg-gradient-to-br from-brand-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
            </div>

            <div class="group bg-zinc-900/80 border border-brand-500/20 p-6 sm:p-8 hover:border-brand-500 transition-all duration-300 shadow-xl flex flex-col justify-between relative overflow-hidden">
                <div class="relative z-10">
                    <div class="size-16 bg-zinc-800 text-brand-500 flex items-center justify-center font-display text-2xl font-bold mb-6">W</div>
                    <h4 class="text-xl font-bold font-display tracking-widest text-white group-hover:text-brand-400 uppercase mb-1 transition-colors">W.S.D.Sithumini (Hashi)</h4>
                    <p class="text-sm font-display tracking-widest uppercase text-gray-500 mb-6">Main Hair desining(female)</p>
                    <p class="text-base font-light text-gray-400 leading-relaxed mb-6">Hashi specializes in ladies hair designing, treatments, and beauty, offering a comprehensive and beautiful experience.</p>
                </div>
                <div class="absolute inset-0 bg-gradient-to-br from-brand-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
            </div>
        </div>
    </div>

    <!-- Contact & Hours Grid -->
    <div data-aos="fade-up" class="grid grid-cols-1 lg:grid-cols-2 gap-16">
        <div>
            <h2 class="text-3xl font-bold text-brand-400 font-display uppercase tracking-widest mb-8 flex items-center gap-4">
                Find us
                <span class="flex-1 h-px bg-brand-500/20"></span>
            </h2>
            <div class="bg-zinc-900/80 border border-brand-500/20 p-6 sm:p-8 shadow-xl grid grid-cols-1 sm:grid-cols-2 gap-6 relative overflow-hidden">
                <div>
                    <p class="text-brand-400 font-display tracking-widest uppercase text-sm mb-2">Address</p>
                    <p class="text-base font-light text-gray-300 leading-relaxed">641 Govinna Road<br>Athurugiriya<br>Sri Lanka</p>
                </div>
                <div>
                    <p class="text-brand-400 font-display tracking-widest uppercase text-sm mb-2">Phone</p>
                    <p class="text-base font-light text-gray-300">075 217 1225</p>
                </div>
                <div>
                    <p class="text-brand-400 font-display tracking-widest uppercase text-sm mb-2">WhatsApp</p>
                    <p class="text-base font-light text-gray-300">075 217 1225</p>
                </div>
                <div class="break-all">
                    <p class="text-brand-400 font-display tracking-widest uppercase text-sm mb-2">Email</p>
                    <p class="text-base font-light text-gray-300">info@salonashi.com</p>
                </div>
            </div>
        </div>
        
        <div>
            <h2 class="text-3xl font-bold text-brand-400 font-display uppercase tracking-widest mb-8 flex items-center gap-4">
                Opening hours
                <span class="flex-1 h-px bg-brand-500/20"></span>
            </h2>
            <div class="bg-zinc-900/80 border border-brand-500/20 p-6 sm:p-8 shadow-xl space-y-6 relative overflow-hidden">
                <div class="flex justify-between items-center border-b border-white/5 pb-4">
                    <span class="text-brand-400 font-display tracking-widest uppercase text-sm">Monday - Sunday</span>
                    <span class="text-base font-light text-gray-300">9:00 AM – 8:00 PM</span>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA -->
    <div data-aos="fade-up" class="group bg-brand-500/5 border border-brand-500/30 p-12 text-center relative overflow-hidden transition-all duration-300 hover:border-brand-500 hover:bg-brand-500/10 shadow-xl">
        <div class="relative z-10">
            <h3 class="text-white text-3xl font-display uppercase tracking-widest font-bold mb-4 group-hover:text-brand-400 transition-colors">Ready for your next look?</h3>
            <p class="text-gray-400 mb-8 font-light text-lg">Book your appointment online in seconds — no calls, no waiting.</p>
            <a href="/book" class="inline-flex items-center gap-2 bg-brand-500 px-8 py-4 text-sm font-semibold text-zinc-950 hover:bg-white transition-colors font-display uppercase tracking-widest shadow-lg shadow-brand-500/20 no-underline">
                Book your appointment <i data-lucide="arrow-right" class="size-4"></i>
            </a>
        </div>
    </div>
</div>
HTML;
    }

    private function defaultTerms(): string
    {
        return <<<HTML
<p>By booking an appointment with us — whether online, by phone or in person — you agree to these terms.</p>
<h3>Bookings &amp; cancellations</h3>
<ul>
  <li>Please arrive 5 minutes before your appointment. Late arrivals may result in a shortened service or rescheduling.</li>
  <li>We require at least 4 hours' notice to cancel or reschedule. Repeated no-shows may affect future bookings.</li>
  <li>Service durations and prices are estimates and may vary based on hair type, length and treatment chosen.</li>
</ul>
<h3>Payment</h3>
<p>Payment is due at the end of your service. We accept cash, card and selected digital wallets.</p>
<h3>Liability</h3>
<p>Please inform your stylist of any allergies or sensitivities before service. We cannot be held responsible for reactions where this information was not disclosed.</p>
<h3>Changes</h3>
<p>We may update these terms from time to time. The latest version always lives on this page.</p>
HTML;
    }

    private function defaultPrivacy(): string
    {
        return <<<HTML
<p>Your privacy matters to us. This page explains what information we collect, why, and how we keep it safe.</p>
<h3>What we collect</h3>
<ul>
  <li><strong>Booking details:</strong> your name, mobile number and (optionally) email address — used only to confirm appointments and send reminders.</li>
  <li><strong>Service history:</strong> the services you've had with us, so we can serve you better next time.</li>
</ul>
<h3>How we use it</h3>
<p>We use your information to manage your bookings, send appointment reminders and (with your permission) occasional offers. We do not sell or share your information with third parties.</p>
<h3>How long we keep it</h3>
<p>We keep your details for as long as you remain a customer. You can ask us to delete your record at any time by contacting us.</p>
<h3>Your rights</h3>
<p>You have the right to access, correct or delete the personal information we hold about you. Get in touch via our contact page.</p>
HTML;
    }

    private function defaultRefund(): string
    {
        return <<<HTML
<p>We want every visit to feel worth it. If something isn't right, please tell us — we'll do our best to put it right.</p>
<h3>Services</h3>
<ul>
  <li>If you're unhappy with a service, let us know within 7 days and we'll offer a complimentary fix where possible.</li>
  <li>Refunds on services are issued at the salon's discretion, on a case-by-case basis.</li>
</ul>
<h3>Product returns</h3>
<ul>
  <li>Unopened retail products may be returned within 14 days with the original receipt for a full refund.</li>
  <li>Opened products cannot be returned for hygiene reasons, unless faulty.</li>
</ul>
<h3>Deposits</h3>
<p>Deposits taken to secure bridal or large bookings are non-refundable but may be transferred to a rescheduled date with 7 days' notice.</p>
<h3>Contact</h3>
<p>For any refund question, please reach out via our contact page or call the salon directly.</p>
HTML;
    }
}

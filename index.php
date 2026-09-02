<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MubeeTech — USSD Service</title>
    <link rel="icon" type="image/png" href="mubeetech_icon.png">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DM Sans', system-ui, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
        }
        .container {
            max-width: 780px;
            background: #ffffff;
            border-radius: 22px;
            padding: 3rem 2.5rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.06);
            text-align: center;
        }
        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            font-size: 1.8rem;
            font-weight: 800;
            color: #2563eb;
            margin-bottom: 0.5rem;
        }
        .logo img {
            height: 50px;
        }
        .logo span {
            color: #1e293b;
        }
        .badge {
            display: inline-block;
            background: #2563eb;
            color: #fff;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 4px 14px;
            border-radius: 20px;
            margin-bottom: 1.5rem;
        }
        h1 {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            font-size: 1.6rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.75rem;
        }
        p {
            color: #64748b;
            font-size: 1rem;
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }
        .code-block {
            background: #f1f5f9;
            padding: 1rem 1.25rem;
            border-radius: 12px;
            font-family: 'Courier New', monospace;
            font-size: 1.1rem;
            color: #0a2540;
            display: inline-block;
            margin-bottom: 1.5rem;
            border: 1px solid #e2e8f0;
        }
        .divider {
            width: 60px;
            height: 3px;
            background: #2563eb;
            border-radius: 2px;
            margin: 1.5rem auto;
        }
        .footer {
            font-size: 0.8rem;
            color: #94a3b8;
            margin-top: 1.5rem;
        }
        .footer a {
            color: #2563eb;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }

        /* ── PRICING SECTION ── */
        .pricing {
            margin-top: 2.5rem;
            padding-top: 2rem;
            border-top: 1px solid #e2e8f0;
        }
        .pricing h2 {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1.5rem;
        }
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }
        .plan {
            background: #f8fafc;
            border-radius: 16px;
            padding: 1.5rem 1rem;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            scroll-margin-top: 20px;
        }
        .plan:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
            border-color: #2563eb;
        }
        .plan.featured {
            border-color: #2563eb;
            background: #f0f7ff;
        }
        .plan h3 {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }
        .plan .price {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            font-size: 1.4rem;
            font-weight: 800;
            color: #2563eb;
            margin-bottom: 1rem;
        }
        .plan .price span {
            font-size: 0.8rem;
            font-weight: 400;
            color: #64748b;
        }
        .plan ul {
            list-style: none;
            padding: 0;
            margin: 0 0 1.2rem 0;
        }
        .plan ul li {
            font-size: 0.85rem;
            color: #475569;
            padding: 4px 0;
            display: flex;
            align-items: center;
            gap: 6px;
            justify-content: center;
        }
        .plan ul li::before {
            content: "✓";
            color: #2563eb;
            font-weight: 700;
        }
        .plan .btn {
            display: inline-block;
            padding: 8px 20px;
            background: #2563eb;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            transition: background 0.3s;
        }
        .plan .btn:hover {
            background: #1d4ed8;
        }
        .plan .btn-outline {
            background: transparent;
            color: #2563eb;
            border: 1px solid #2563eb;
        }
        .plan .btn-outline:hover {
            background: #2563eb;
            color: #fff;
        }

        /* ── CONTACT SECTION ── */
        .contact-section {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
            text-align: center;
        }
        .contact-section h2 {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            font-size: 1.2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }
        .contact-section p {
            color: #64748b;
            margin-bottom: 0.25rem;
        }
        .contact-section a {
            color: #2563eb;
            text-decoration: none;
        }
        .contact-section a:hover {
            text-decoration: underline;
        }

        @media (max-width: 640px) {
            .pricing-grid {
                grid-template-columns: 1fr;
                max-width: 320px;
                margin: 0 auto;
            }
            .container {
                padding: 2rem 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="mubeetech_icon.png" alt="MubeeTech" />
            Mubee<span>Tech</span>
        </div>
        <div class="badge">USSD Service</div>

        <h1>Factory Stock Check</h1>

        <p>
            This is the USSD callback endpoint for the<br />
            <strong>Factory Stock Check</strong> service.
        </p>

        <div class="code-block">
            *384*45696#
        </div>

        <p style="font-size: 0.9rem;">
            Dial the code above on your phone to check factory stock levels, request restocks, and manage inventory — all via USSD.
        </p>

        <div class="divider"></div>

        <!-- PRICING SECTION -->
        <div class="pricing">
            <h2>Simple Pricing for Every Factory</h2>
            <div class="pricing-grid">
                <div class="plan" id="starter">
                    <h3>Starter</h3>
                    <div class="price">₦25,000<span>/month</span></div>
                    <ul>
                        <li>Up to 50 materials</li>
                        <li>100 USSD requests/day</li>
                        <li>Email support</li>
                    </ul>
                    <a href="#starter" class="btn">Get Started</a>
                </div>
                <div class="plan featured" id="business">
                    <h3>Business</h3>
                    <div class="price">₦50,000<span>/month</span></div>
                    <ul>
                        <li>Unlimited materials</li>
                        <li>500 USSD requests/day</li>
                        <li>SMS alerts</li>
                        <li>Priority support</li>
                    </ul>
                    <a href="#business" class="btn">Get Started</a>
                </div>
                <div class="plan" id="enterprise">
                    <h3>Enterprise</h3>
                    <div class="price">₦100,000<span>/month</span></div>
                    <ul>
                        <li>Custom deployment</li>
                        <li>Dedicated support</li>
                        <li>Custom features</li>
                    </ul>
                    <a href="#enterprise" class="btn btn-outline">Contact Us</a>
                </div>
            </div>
        </div>

        <!-- CONTACT SECTION -->
        <div class="contact-section" id="contact">
            <h2>📬 Get In Touch</h2>
            <p>Email: <a href="mailto:info@mubeetech.com.ng">info@mubeetech.com.ng</a></p>
            <p>Phone: <a href="tel:+2349034394409">+234 903 439 4409</a></p>
        </div>

        <p style="font-size: 0.85rem; color: #94a3b8; margin-top: 1.5rem;">
            For technical inquiries, please refer to the API documentation or contact support.
        </p>

        <div class="footer">
            &copy; 2026 <a href="https://mubeetech.com.ng" target="_blank">MubeeTech</a> &bull; Built for Africa's Talking Hackathon
        </div>
    </div>
</body>
</html>
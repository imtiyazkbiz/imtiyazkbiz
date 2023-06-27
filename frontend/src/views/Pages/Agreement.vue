<template>
  <div class="row">
    <div class="col-md-12" id="serviceAgreement" v-if="type == 'company'">
      <p>
        This Service Activation Agreement (the “Agreement”) is entered into on
        the date user selected to “Create Account” on
        <a :href="'http://' + siteUrl" target="__blank">{{ siteUrl }}</a
        >, (“Effective Date”) by and between {{ siteName }}, LLC. (“{{
          siteName
        }}”) and the company set forth in the Sign Up page (“Company”) for the
        purpose of providing access to {{ siteName }}’s online training courses
        as identified in this Agreement.
      </p>
      <ol>
        <li>
          Service Fees:
          <p>
            The basic terms of this Agreement are based upon the number of
            locations, users and courses entered by the Company on the Sign Up
            page.{{ siteName }} reserves the right to modify the Service Fees if
            the Company adds additional locations, users or courses to the
            account. By creating the account, Company agrees to pay either the
            Total Cost per Month or Yearly Cost (collectively referred to as
            “Service Fees”) as set forth in paragraph 3 below. All pricing is
            based upon payments made through either credit card or ACH. If
            Company selects to be invoiced for the Service Fees, an additional
            10% fee will be added to the cost.
          </p>
        </li>
        <li>
          Term and Termination:
          <p>
           
			This Agreement shall begin on the Effective Date set forth above and continue for one (1) year thereafter. 
			Company agrees to pay {{ siteName }} the Service Fees set forth in paragraph 1 above (“Initial Term”). 
			At the expiration of the Initial Term, the Agreement shall automatically renew for one-year periods (“Renewal Periods”). 
			In the event Company desires to cancel the {{ siteName }}’s services, it shall provide written notice to {{ siteName }},
			which shall not be less than thirty (30) days prior to the desired cancellation date (“Cancellation Notice”).  
			If the Cancellation Notice is received during the Initial Term, 
			a cancellation fee will be charged to Company which will be equal to the sum of the remaining term of the Agreement. 
			If the cancellation notice is received during a Renewal Period, 
			the Company agrees to pay a cancellation fee in the amount of the sum of the most recent four months’ charges.  
			The minimum cancellation fee for either is $500.
          </p>
        </li>

        <li>
          Payments and Terms:
          <p>
            Company understands and agrees that if it selects to pay the Service
            Fees yearly, then payment is due prior to the account being
            activated. If Company selects to pay the Service Fees monthly, then
            monthly payments are due on the 1st day of every month during the
            Initial Term and any Renewal Period. Any payments made after the 5th
            day of the month, will be considered late and subject to the late
            fees set forth below. Company shall pay {{ siteName }} monthly
            either by automatic credit card payment or by automatic debit
            (“ACH”) from Company’s checking account through the automated
            clearing house system (a credit card will also be needed on file as
            a backup payment in the unlikely case the ACH payment does not go
            through). {{ siteName }} will charge the Company’s credit card or
            checking account on the first of each month and every month
            thereafter. Accounts not paid in full within five days of the
            Monthly Service Fee may be placed on hold and {{ siteName }} may
            immediately terminate service and access to the website.
          </p>

          <p>
            Late Payment: Company will pay a late fee of [one percent (1%) per
            month OR eighteen percent (18%) per annum] or the highest rate
            allowed under the law, whichever is higher, on any overdue amounts.
            Company also agrees to pay {{ siteName }} all reasonable costs and
            expenses of collection, including attorneys' fees and costs
            incurred, whether pre-litigation, trial, appeal or post-litigation.
          </p>
        </li>

        <li>
          Additional Terms:
          <p>
            Company agrees that {{ siteName }} is the exclusive owner of the
            videos, courses, tests, answers and all intellectual property
            associated therewith. Nothing in this Agreement shall be construed
            as providing Company with any ownership claims to any of
            {{ siteName }}’s videos, courses, tests, answers or intellectual
            property. Any and all videos created with Company’s logo, are the
            sole and exclusive property of {{ siteName }} and shall not be
            considered a “work for hire” under any circumstance. Company’s use
            of{{ siteName }}’s services is subject to the Terms and Conditions
            currently located on {{ siteName }}’s website at
            <a :href="'http://' + siteUrl" target="__blank">{{ siteUrl }}</a
            >. Company acknowledges and agrees that the Terms and Conditions of
            the {{ siteName }} website, will govern Company’s use of
            {{ siteName }} website and services. Further, Company acknowledges
            and agrees that it is the Company’s obligation to ensure that their
            employees successfully complete {{ siteName }}’s online training
            courses and to otherwise comply with all state mandated training
            requirements. If any requirement is not met, Company agrees that
            {{ siteName }}, LLC will not be held liable in any manner.
          </p>
        </li>

        <li>
          Certain Usage, Billing and Refunds:
          <p>
            Billing is based on the Service Fees set forth above. All Service
            Fees and video fees are non-refundable. Any promotional products
            provided to Company, i.e. tablets, are provided in an “As-Is”
            condition and Company acknowledges that {{ siteName }} does not
            provide any refunds, exchanges or repairs to said products. In the
            event a username or password is shared with a user not authorized by
            {{ siteName }}, {{ siteName }} may deactivate the Company’s account
            without issuing a refund. Company hereby agrees that it has read,
            understands fully and agrees to the Terms and Conditions on the
            {{ siteName }} website and/or for the product or service purchased
            by Company.
          </p>
        </li>

        <li>
          Governing Law:
          <p>
            This Agreement shall be governed by and construed under the laws of
            the State of Florida, without giving effect to principles of
            conflicts of law. Further, exclusive venue for any litigation
            permitted under this Agreement shall be Palm Beach County, Florida.
            Each party irrevocably and unconditionally: (i) agrees that any
            suit, action or other legal proceeding arising out of or relating to
            this Agreement shall be brought in the Courts of record of the State
            of Florida in Palm Beach or the courts of the United States District
            Court for the Southern District of Florida; (ii) consents to the
            jurisdiction of each such court in any such suit, action or
            proceeding; (iii) waives any objection which it may have to the
            laying of venue of any such suit, action or proceeding in any such
            courts; and (iv) agrees that service of any court paper may be
            effected on such party by any manner as may be provided under
            applicable laws or court rules in said State.
          </p>

          <p>
            IN WITNESS WHEREOF, the Company, intending to be legally bound
            hereby, has caused this Agreement to be electronically executed by
            its duly authorized representative as of the Effective Date first
            set forth above by clicking “Accept” the terms of this agreement.
          </p>
        </li>
      </ol>
    </div>
    <div class="col-md-12" id="serviceAgreement" v-else>
      <div class="WordSection1">
        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >AGREEMENT BETWEEN USER AND {{ siteName }}</span
          >
        </p>
        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;">Welcome to </span
          ><span lang="EN-US"
            ><a :href="'http://' + siteUrl" target="__blank">{{
              siteUrl
            }}</a></span
          ><span lang="EN-US" style="font-weight: 400;"
            >.  The {{ siteName }} website (the “Site”) is comprised of various
            web pages operated by {{ siteName }}, LLC (“TRAIN”).  The Site is
            offered to you conditioned upon your acceptance without modification
            of the terms, conditions and notices contained herein (the
            “Terms”).  By accessing and using </span
          ><span lang="EN-US"
            ><a :href="'http://' + siteName" target="__blank">{{
              siteUrl
            }}</a></span
          ><span lang="EN-US" style="font-weight: 400;">
            you accept and agree to be bound by the terms and provisions
            contained herein.  Please read the Terms carefully and keep a copy
            of them for your reference.
          </span>
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >“Services” means TRAIN’S hosted internet accessible learning
            management solution service, video course material, tests, and any
            and all curriculum or other resources available on a subscription
            term basis pursuant to the Service Activation Agreement, available
            at </span
          ><span lang="EN-US"
            ><a :href="'http://' + siteUrl" target="__blank"
              ><span style="font-weight: 400;">{{ siteUrl }}</span></a
            ></span
          ><span class="MsoHyperlink"
            ><span lang="EN-US" style="font-weight: 400;">.</span></span
          ><span lang="EN-US" style="font-weight: 400;"> </span>
        </p>
        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >An Authorized User means any individual, company, company employee,
            contractor, agent or any other individual authorized to access and
            use the Services, via company’s or individual’s purchased
            subscriptions, for the purpose specified herein. Company clients are
            responsible for their Authorized Users’ compliance with this
            Agreement.</span
          >
        </p>
        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >TRAIN reserves the right to update and change the Terms from time
            to time without notice. Any new features that augment or enhance the
            current Service, including the release of new tools and resources,
            shall be subject to the Terms. Continued use of the Services after
            any such changes shall constitute your consent to such changes.
            Violation of any of the terms below may result in the termination of
            your Account.
          </span>
        </p>
        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;">PRIVACY</span>
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >For information about how we collect, use, share or otherwise
            process information about you, please see our Privacy Policy.
          </span>
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >ELECTRONIC COMMUNICATIONS</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;">Visiting </span
          ><span lang="EN-US"
            ><a :href="'http://' + siteUrl" target="__blank"
              ><span style="font-weight: 400;">{{ siteUrl }}</span></a
            ></span
          ><span lang="EN-US" style="font-weight: 400;">
            or sending emails to {{ siteName }}, LLC constitutes electronic
            communications.  You consent to receive electronic communications
            and you agree that all agreements, notices, disclosures and other
            communications that we provide to you electronically, via email and
            on the Site, satisfy any legal requirement that such communications
            be in writing.
          </span>
        </p>
        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;">YOUR ACCOUNT</span>
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >If you use the Services and Site, you are responsible for
            maintaining the confidentiality of your account and password and for
            restricting access to your computer, and you agree to accept
            responsibility for all activities that occur under your account or
            password.  You may not assign or otherwise transfer your account to
            any other person or entity.  You acknowledge that TRAIN is not
            responsible for third party access to your account that results from
            theft or misappropriation of your account.  TRAIN and its associates
            reserve the right to refuse or cancel the service, terminate
            accounts or remove or edit content in our sole discretion.  TRAIN
            does not knowingly collect, either online or offline, personal
            information from persons under the age of thirteen. If you are under
            18, you may use the Site only with permission of a parent or
            guardian.
          </span>
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;">MISUSE</span>
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >You must not misuse the Services or Site. For example, you must
            not:</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >(a) copy, modify, host, stream, sublicense, or resell the Services
            or Site;</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >(b) enable or allow others to use the Services or Site using your
            account information;</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >(c) use the Site to construct any kind of database;</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >(d) access or attempt to access the Services or Site by any means
            other than the interface we provide or authorize;</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >(e) circumvent any access or use restrictions put into place to
            prevent certain uses of the Services or Site;</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >(f) share Content, or engage in behavior that violates anyone’s
            intellectual property rights (“<b>Intellectual Property Rights</b>”
            means copyright, moral rights, trademark, trade dress, patent, trade
            secret, unfair competition, right of privacy, right of publicity,
            and any other proprietary rights);</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >(g) upload or share any Content that is unlawful, harmful,
            threatening, abusive, tortious, defamatory, libelous, vulgar, lewd,
            profane, invasive of another’s privacy, or hateful;</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >(h) impersonate any person or entity, or falsely state or otherwise
            misrepresent your affiliation with a person or entity;</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >(i) attempt to disable, impair, or destroy the Services or
            Site;</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >(j) upload, transmit, store, or make available any Content or code
            that contains any viruses, malicious code, malware, or any
            components designed to harm or limit the functionality of the
            Services or Site;</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >(k) disrupt, interfere with, or inhibit any other user from using
            the Services or Site (such as stalking, Intimidating, or harassing
            others, inciting others to commit violence, or harming minors in any
            way);</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >(l) engage in chain letters, junk mails, pyramid schemes, phishing,
            spamming, or other unsolicited messages;</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >(m) place an advertisement of any products or services in the
            Services or Site except with our prior written approval;</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >(n) use any data mining or similar data gathering and extraction
            methods in connection with the Services; or</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >(o) violate applicable law (including, but not limited to, where
            applicable, COPPA).</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >LINKS TO THIRD PARTY SITES/THIRD PARTY SERVICES</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >The Site may contain links to other websites (“Linked Sites”). The
            Linked Sites are not under TRAIN’s control and TRAIN is not
            responsible for the contents of any Linked Site, including without
            limitation any link contained in a Linked Site, or any changes or
            updates to a Linked Site. TRAIN is providing these links to you only
            as a convenience, and the inclusion of any link does not imply
            endorsement by TRAIN of the site or any association with its
            operators.</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >Certain services made available on the Site are delivered by third
            party sites and organizations. By using any product, service or
            functionality originating from the Site, you hereby acknowledge and
            consent that TRAIN may share such information and data with any
            third party with whom TRAIN has a contractual relationship to
            provide the requested product, service or functionality on behalf of
            the Site’s Authorized Users and customers.</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >NO UNLAWFUL OR PROHIBITED USE/INTELLECTUAL PROPERTY</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >You are granted a non-exclusive, non-transferable, revocable
            license to access and use
            <a :href="'http://' + siteUrl" target="__blank">{{ siteUrl }}</a>
            strictly in accordance with these terms of use. As a condition of
            your use of the Site, you warrant to TRAIN that you will not use the
            Site for any purpose that is unlawful or prohibited by these Terms.
            You may not use the Site in any manner which could damage, disable,
            overburden, or impair the Site or interfere with any other party’s
            use and enjoyment of the Site. You may not obtain or attempt to
            obtain any materials or information through any means not
            intentionally made available or provided for through the Site.</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >All content included as part of the Service, such as text,
            graphics, logos, images, videos as well as the compilation thereof,
            and any software used on the Site, is the property of TRAIN or its
            suppliers and protected by copyright, trademark and other laws that
            protect intellectual property and proprietary rights. You agree to
            observe and abide by all copyright and other proprietary notices,
            legends or other restrictions contained in any such content and will
            not make any changes thereto.</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >You will not modify, publish, transmit, reverse engineer,
            participate in the transfer or sale, create derivative works, or in
            any way exploit any of the content, in whole or in part, found on
            the Site.  TRAIN’s content is not for resale. Your use of the Site
            does not entitle you to make any unauthorized use of any protected
            content, and you will not delete or alter any proprietary rights or
            attribution notices in any content. You will use protected content
            solely for your personal use and will make no other use of the
            content without the express written permission of TRAIN and the
            copyright owner. You agree that you do not acquire any ownership
            rights in any protected content. We do not grant you any licenses,
            express or implied, to the intellectual property of TRAIN or our
            licensors except as expressly authorized by these Terms.</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >INTERNATIONAL USERS</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >The Service is controlled, operated and administered by TRAIN from
            our offices within the USA. If you access the Service from a
            location outside the USA, you are responsible for compliance with
            all local laws. You agree that you will not use the TRAIN Content
            accessed through
            <a :href="'http://' + siteUrl" target="__blank">{{ siteUrl }}</a> in
            any country or in any manner prohibited by any applicable laws,
            restrictions or regulations.</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;">INDEMNIFICATION</span>
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >You agree to indemnify, defend and hold harmless TRAIN, its
            officers, directors, employees, agents and third parties, for any
            losses, costs, liabilities and expenses (including reasonable
            attorneys' fees) relating to or arising out of your use of or
            inability to use the Site or Services, any user postings made by
            you, your violation of any terms of this Agreement or your violation
            of any rights of a third party, or your violation of any applicable
            laws, rules or regulations. TRAIN reserves the right, at its own
            cost, to assume the exclusive defense and control of any matter
            otherwise subject to indemnification by you, in which event you will
            fully cooperate with TRAIN in asserting any available
            defenses.</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >LIABILITY DISCLAIMER</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >THE INFORMATION, SOFTWARE, PRODUCTS, AND SERVICES INCLUDED IN OR
            AVAILABLE THROUGH THE SITE MAY INCLUDE INACCURACIES OR TYPOGRAPHICAL
            ERRORS. CHANGES ARE PERIODICALLY ADDED TO THE INFORMATION HEREIN.
            TRAIN AND/OR ITS SUPPLIERS MAY MAKE IMPROVEMENTS AND/OR CHANGES IN
            THE SITE AT ANY TIME.</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >TRAIN AND/OR ITS SUPPLIERS MAKE NO REPRESENTATIONS ABOUT THE
            SUITABILITY, RELIABILITY, AVAILABILITY, TIMELINESS, AND ACCURACY OF
            THE COURSES, VIDEOS, INFORMATION, SOFTWARE, PRODUCTS, SERVICES AND
            RELATED GRAPHICS CONTAINED ON THE SITE FOR ANY PURPOSE. TO THE
            MAXIMUM EXTENT PERMITTED BY APPLICABLE LAW, ALL SUCH COURSES,
            VIDEOS, INFORMATION, SOFTWARE, PRODUCTS, SERVICES AND RELATED
            GRAPHICS ARE PROVIDED &quot;AS IS&quot; WITHOUT WARRANTY OR
            CONDITION OF ANY KIND. TRAIN AND/OR ITS SUPPLIERS HEREBY DISCLAIM
            ALL WARRANTIES AND CONDITIONS WITH REGARD TO THE COURSES, VIDEOS,
            INFORMATION, SOFTWARE, PRODUCTS, SERVICES AND RELATED GRAPHICS,
            INCLUDING ALL IMPLIED WARRANTIES OR CONDITIONS OF MERCHANTABILITY,
            FITNESS FOR A PARTICULAR PURPOSE, TITLE AND NON-INFRINGEMENT.</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >TO THE MAXIMUM EXTENT PERMITTED BY APPLICABLE LAW, IN NO EVENT
            SHALL TRAIN AND/OR ITS SUPPLIERS BE LIABLE FOR ANY DIRECT, INDIRECT,
            PUNITIVE, INCIDENTAL, SPECIAL, CONSEQUENTIAL, LIQUIDATED OR
            UNLIQUIDATED DAMAGES OR ANY DAMAGES WHATSOEVER INCLUDING, WITHOUT
            LIMITATION, DAMAGES FOR LOSS OF USE, DATA OR PROFITS, ARISING OUT OF
            OR IN ANY WAY CONNECTED WITH THE USE OR PERFORMANCE OF THE SITE,
            WITH THE DELAY OR INABILITY TO USE THE SITE OR RELATED SERVICES, THE
            PROVISION OF OR FAILURE TO PROVIDE SERVICES, OR FOR ANY COURSES,
            VIDESO, INFORMATION, SOFTWARE, PRODUCTS, SERVICES AND RELATED
            GRAPHICS OBTAINED THROUGH THE SITE, OR OTHERWISE ARISING OUT OF THE
            USE OF THE SITE, WHETHER BASED ON CONTRACT, TORT, NEGLIGENCE, STRICT
            LIABILITY OR OTHERWISE, EVEN IF TRAIN OR ANY OF ITS SUPPLIERS HAS
            BEEN ADVISED OF THE POSSIBILITY OF DAMAGES. BECAUSE SOME
            STATES/JURISDICTIONS DO NOT ALLOW THE EXCLUSION OR LIMITATION OF
            LIABILITY FOR CONSEQUENTIAL OR INCIDENTAL DAMAGES, THE ABOVE
            LIMITATION MAY NOT APPLY TO YOU. IF YOU ARE DISSATISFIED WITH ANY
            PORTION OF THE SITE, OR WITH ANY OF THESE TERMS OF USE, YOUR SOLE
            AND EXCLUSIVE REMEDY IS TO DISCONTINUE USING THE SITE.</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >CPR TRAINING VIDEOS</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >The CPR training on the Site is not a replacement for completing an
            authorized and accredited CPR course in your local area</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;">NO ADVICE</span>
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >This Site may contain general information about medical conditions
            and treatments. The information is not advice and should not be
            treated as such. Our online CPR courses are not a substitute for
            hands-on CPR training by an accredited training provider and should
            not be used as such.</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >LIMITATION OF WARRANTIES</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >The medical information on this Site is provided “as is” without
            any representations or warranties, express or implied.  TRAIN makes
            no representations or warranties in relation to the medical
            information on this Site or in our online first aid course.</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >Without prejudice to the generality of the foregoing paragraph,
            TRAIN does not warrant that: (1) the medical information on this
            website will be constantly available, or available at all; or (2)
            the medical information on this website is complete, true, accurate,
            up-to-date, or non-misleading.</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >SEEKING PROFESSIONAL ASSISTANCE</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >You must not rely on the information on this Site as an alternative
            to medical advice from your doctor or other healthcare professional.
            If you have any specific questions about any medical matter you
            should consult your doctor or other healthcare professional. If you
            think you may be suffering from any medical condition you should
            seek immediate medical attention from a qualified healthcare
            professional. You should never delay seeking medical advice,
            disregard medical advice, or discontinue medical treatment because
            of information on this Site or in our online first aid course.</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >OUR CPR CERTIFICATE</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >Our CPR certificate does not prove competency in any CPR or first
            aid skill. Our CPR certificate is not recognized by any
            organizations or training providers. We do not provide any formal
            CPR qualifications and you must not use our certificate for this.
            This Site is not a replacement for an authorized and accredited CPR
            course.</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >TERMINATION/ACCESS RESTRICTION</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >TRAIN reserves the right, in its sole discretion, to terminate your
            access to the Site and the related services or any portion thereof
            at any time, without notice. To the maximum extent permitted by law,
            this agreement is governed by the laws of the State of Florida and
            you hereby consent to the exclusive jurisdiction and venue of courts
            in Broward County, Florida in all disputes arising out of or
            relating to the use of the Site. Use of the Site is unauthorized in
            any jurisdiction that does not give effect to all provisions of
            these Terms, including, without limitation, this section.</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >You agree that no joint venture, partnership, employment, or agency
            relationship exists between you and TRAIN as a result of this
            Agreement or use of the Site. TRAIN's performance of this Agreement
            is subject to existing laws and legal process, and nothing contained
            in this Agreement is in derogation of TRAIN's right to comply with
            governmental, court and law enforcement requests or requirements
            relating to your use of the Site or information provided to or
            gathered by TRAIN with respect to such use. If any part of this
            agreement is determined to be invalid or unenforceable pursuant to
            applicable law including, but not limited to, the warranty
            disclaimers and liability limitations set forth above, then the
            invalid or unenforceable provision will be deemed superseded by a
            valid, enforceable provision that most closely matches the intent of
            the original provision and the remainder of the agreement shall
            continue in effect.</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >Unless otherwise specified herein, this Agreement constitutes the
            entire agreement between the User and TRAIN with respect to the Site
            and it supersedes all prior or contemporaneous communications and
            proposals, whether electronic, oral or written, between the User and
            TRAIN with respect to the Site. A printed version of this Agreement
            and of any notice given in electronic form shall be admissible in
            judicial or administrative proceedings based upon or relating to
            this Agreement to the same extent and subject to the same conditions
            as other business documents and records originally generated and
            maintained in printed form. It is the express wish to the parties
            that this Agreement and all related documents be written in English.
          </span>
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;">CHANGES TO TERMS</span>
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >TRAIN reserves the right, in its sole discretion, to change the
            Terms under which the Site is offered. The most current version of
            the Terms will supersede all previous versions. TRAIN encourages you
            to periodically review the Terms to stay informed of our
            updates.</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;">CONTACT US </span>
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >TRAIN welcomes your questions or concerns regarding the Terms &amp;
            Conditions:
          </span>
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >{{ siteName }}, LLC</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >23123 State Road 7, Suite 350A</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >Boca Raton, FL 33433</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >844-99-TRAIN (87246)</span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US"
            ><a :href="'mailto:' + infoEmail"
              ><span style="font-weight: 400;">{{ infoEmail }}</span></a
            ></span
          >
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US"
            ><a :href="'http://' + siteUrl" target="__blank">{{
              siteUrl
            }}</a></span
          ><span lang="EN-US" style="font-weight: 400;"> </span>
        </p>

        <p class="MsoNormal" style="text-align:justify">
          <span lang="EN-US" style="font-weight: 400;"
            >Effective as of October 5, 2020</span
          >
        </p>
      </div>
    </div>
  </div>
</template>
<script>
import { Dynamic } from "../../wl";
export default {
  name: "agreement",
  props: {
    type: String
  },
  data() {
    return {
      siteName: "",
      siteEmail: "",
      siteUrl: ""
    };
  },
  created: function() {
    this.siteName = Dynamic.SITE_NAME;
    this.siteUrl = Dynamic.SITE_URL;
    this.infoEmail = Dynamic.INFO_EMAIL;
  }
};
</script>
<style scoped>
.agreement-content {
  height: 370px;
  overflow-y: scroll;
  overflow-x: hidden;
  padding: 18px;
}
</style>

/**
 * Lusaka South University College - News & Events Manager
 * Handles data management, CRUD operations, and display logic for news/events
 * Uses localStorage for data persistence
 */

// ============================================
// DATA STRUCTURE & INITIALIZATION
// ============================================

const LSUC_EVENTS_STORAGE_KEY = 'lsuc_events_data_v10'; // bumped to v10: changed event image to cpd.jpg
const LSUC_ADMIN_AUTH_KEY = 'lsuc_admin_authenticated';

// Sample minimal data (2-3 events per category)
const SAMPLE_EVENTS = [
    {
        id: "evt_013",
        title: "Aug–Dec 2026 Short Course Drive / Capacity Building Enhancement",
        date: "2026-08-01",
        category: "Upcoming Event",
        shortDescription: "Join our Aug–Dec 2026 Short Course Drive and Capacity Building Enhancement programmes.",
        fullDescription: "<p>We are pleased to notify you of the upcoming Short Courses/CPD programmes scheduled to run from August to December 2026.</p><h3>Capacity Building Enhancement</h3><p>The revised short courses delivery schedule is now active. Please check for more comprehensive information from the <strong>CPD &amp; Short Courses</strong> PDF in the downloads section of our website.</p>",
        image: "./img/cpd.jpg",
        author: "Director",
        pdfLink: "docs/cpd-short-courses.pdf",
        featured: true
    },
    {
        id: "evt_012",
        title: "Share the LSUC Experience & Get Rewarded!",
        date: "2026-06-08",
        category: "Latest News",
        shortDescription: "Refer a friend to Lusaka South University College and choose your reward: a 10% discount on your tuition fees OR 50% of their application fees back in cash — plus exclusive access to campus events and extracurricular activities!",
        fullDescription: `<div style="font-family: inherit; line-height: 1.8; color: #2d3748;">
  <div style="text-align:center; margin-bottom:28px;">
    <img src="./img/advert.jpeg" alt="LSUC Referral Rewards Programme" style="width:100%; max-width:820px; border-radius:14px; box-shadow:0 8px 32px rgba(0,0,0,0.15);">
  </div>

  <p style="font-size:1.1rem; font-weight:600; color:#1a202c; margin-bottom:16px;">At Lusaka South University College, we believe education is better when shared. Refer a friend to join our community and choose the reward that suits you best:</p>

  <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin:24px 0;">
    <div style="background:linear-gradient(135deg,#fff7e6,#ffe8b3); border-left:4px solid #f59e0b; border-radius:10px; padding:20px;">
      <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        <strong style="color:#b45309; font-size:1rem;">Option A</strong>
      </div>
      <p style="margin:0; color:#92400e;">Get a <strong>10% DISCOUNT</strong> on your tuition fees.</p>
    </div>
    <div style="background:linear-gradient(135deg,#f0fdf4,#bbf7d0); border-left:4px solid #22c55e; border-radius:10px; padding:20px;">
      <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="2"/><path d="M12 12h.01"/><path d="M17 12h.01"/><path d="M7 12h.01"/></svg>
        <strong style="color:#15803d; font-size:1rem;">Option B</strong>
      </div>
      <p style="margin:0; color:#166534;">Earn <strong>50% of their application fees</strong> back in cash!</p>
    </div>
  </div>

  <div style="background:#f8fafc; border-radius:12px; padding:24px; margin:24px 0; border:1px solid #e2e8f0;">
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
      <h3 style="margin:0; color:#7c3aed; font-size:1.1rem;">But that's not all! By referring a peer, you both unlock:</h3>
    </div>
    <ul style="list-style:none; padding:0; margin:0;">
      <li style="display:flex; align-items:center; gap:10px; padding:10px 0; border-bottom:1px solid #e2e8f0;">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2e8b57" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        <span>Exclusive access to campus academic events</span>
      </li>
      <li style="display:flex; align-items:center; gap:10px; padding:10px 0;">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2e8b57" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        <span>Priority entry to all extracurricular activities and clubs</span>
      </li>
    </ul>
  </div>

  <p style="font-size:1.05rem; color:#374151; margin:20px 0;">Help us grow the next generation of successful graduates — world over!</p>

  <div style="background:linear-gradient(135deg,#2e8b57,#1a5c38); border-radius:12px; padding:24px; color:#fff; margin-top:28px;">
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ffd700" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.21 3.18 2 2 0 0 1 3.18 1h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.1a16 16 0 0 0 8 8l.49-.49a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 17.92z"/></svg>
      <strong style="font-size:1.1rem; color:#ffd700;">Get in Touch</strong>
    </div>
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
      <div style="display:flex; align-items:center; gap:8px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#a7f3d0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.21 3.18 2 2 0 0 1 3.18 1h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.1a16 16 0 0 0 8 8l.49-.49a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 17.92z"/></svg>
        <span><strong>Call:</strong> +260 770 359518</span>
      </div>
      <div style="display:flex; align-items:center; gap:8px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#a7f3d0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        <span><strong>Email:</strong> lusakasouthuniversity@gmail.com</span>
      </div>
      <div style="display:flex; align-items:center; gap:8px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#a7f3d0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
        <span><strong>Website:</strong> <a href="https://www.lsc.edu.zm" style="color:#ffd700; text-decoration:none;">www.lsc.edu.zm</a></span>
      </div>
      <div style="display:flex; align-items:center; gap:8px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#a7f3d0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        <span>Visit us in Lusaka for more details</span>
      </div>
    </div>
  </div>

  <div style="margin-top:20px; display:flex; flex-wrap:wrap; gap:8px;">
    ${['LSUC','LusakaSouthUniversity','ReferAFriend','ZambiaEducation','HigherLearning','StudentRewards'].map(tag => `<span style="background:#e0f2fe; color:#0369a1; padding:4px 12px; border-radius:20px; font-size:0.85rem; font-weight:600;">#${tag}</span>`).join('')}
  </div>
</div>`,
        image: "./img/advert.jpeg",
        author: "Communications Office",
        featured: true
    },
    {
        id: "evt_011",
        title: "April 2026 First Year Student Orientation — A Remarkable Success",
        date: "2026-05-13",
        category: "Latest News",
        shortDescription: "LSUC successfully conducted its first year student orientation for the April 2026 intake on 13th May 2026, featuring career talks from MTN, Prudential, Absa, Sancho Medical Services, and the Drug Enforcement Commission (DEC).",
        fullDescription: "<p>On <strong>Tuesday, 13th May 2026</strong>, Lusaka South University College (LSUC) proudly hosted its <strong>First Year Student Orientation</strong> for the <strong>April 2026 Intake</strong> — an event that proved to be an extraordinary milestone for our new students.</p><p>What made this orientation truly exceptional was the rare privilege extended to our students: a series of expert-led talks by industry leaders and government institutions, giving our first-year students an invaluable head-start beyond academics.</p><div style='text-align:center; margin: 24px 0;'><img src='./img/orientation_1.jpg' alt='April 2026 Orientation — Group Photo' style='width:100%; max-width:780px; border-radius:12px; box-shadow:0 6px 24px rgba(0,0,0,0.15);'><p style='margin-top:10px; color:#6c757d; font-size:0.9rem; font-style:italic;'>April 2026 Intake — First Year Student Orientation, 13th May 2026</p></div><h3>🏢 Guest Speakers &amp; Industry Talks</h3><ul><li><strong>MTN Zambia (HR Department) — Career Talk:</strong> The Human Resources team from MTN Zambia engaged students on career development, professional readiness, and navigating the corporate world after graduation.</li><li><strong>Prudential Life Insurance — Insurance Awareness:</strong> Representatives from Prudential delivered an insightful session on the importance of life insurance, financial protection, and planning for the future.</li><li><strong>Absa Bank Zambia — Saving &amp; Investing:</strong> Absa Bank's team educated students on smart financial habits — the importance of saving, investing early, and building a secure financial future.</li><li><strong>Dr. David Muchila, Sancho Medical Services — Health Talk:</strong> Dr. Muchila delivered a comprehensive health awareness session covering student wellness, preventive healthcare, and the importance of maintaining physical and mental health.</li><li><strong>Drug Enforcement Commission (DEC) — Drug Abuse Awareness:</strong> DEC officers sensitised students on the dangers of drug abuse, legal consequences, and how to make responsible lifestyle choices.</li></ul><div style='display:grid; grid-template-columns:1fr 1fr; gap:20px; margin:28px 0;'><div><img src='./img/orientation_2.jpg' alt='MTN HR Career Talk' style='width:100%; border-radius:10px; box-shadow:0 4px 16px rgba(0,0,0,0.12);'><p style='margin-top:8px; color:#6c757d; font-size:0.85rem; font-style:italic; text-align:center;'>MTN HR Representative delivering the Career Talk</p></div><div><img src='./img/orientation_3.jpg' alt='Dr. David Muchila — Health Talk' style='width:100%; border-radius:10px; box-shadow:0 4px 16px rgba(0,0,0,0.12);'><p style='margin-top:8px; color:#6c757d; font-size:0.85rem; font-style:italic; text-align:center;'>Dr. David Muchila from Sancho Medical Services — Health Awareness Talk</p></div></div><h3>🎓 LSUC's Own Orientation Programme</h3><p>In addition to the guest sessions, the college itself played its part in fully preparing students for academic life. Students were oriented on:</p><ul><li>Academic expectations, rules, and regulations at LSUC</li><li>ICT services and student portal access available to them</li><li>Student support structures and available resources on campus</li></ul><h3>🌟 A Day of Opportunity</h3><p>The orientation was a testament to LSUC's commitment not just to academic excellence but to the holistic development of every student. By connecting new students with industry professionals and institutions from day one, LSUC continues to set the standard for quality higher education in Zambia.</p><p>We welcome all April 2026 first-year students to the LSUC family and wish you a fulfilling and successful academic journey!</p>",
        image: "./img/orientation_1.jpg",
        author: "Communications Office",
        featured: true
    },
    {
        id: "evt_001",
        title: "International Culture Day 2025",
        date: "2025-09-26",
        category: "Latest News",
        shortDescription: "We successfully celebrated the rich diversity of our traditions, food, music, and art. The event featured vibrant cultural performances, exhibitions, and activities.",
        fullDescription: "<p>The International Culture Day 2025 was a tremendous success, bringing together students, faculty, and community members to celebrate the rich diversity of cultures represented at Lusaka South University College.</p><h3>Event Highlights:</h3><ul><li>Traditional dance performances from 15+ countries</li><li>International food fair featuring cuisines from around the world</li><li>Art exhibitions showcasing cultural artifacts and contemporary works</li><li>Panel discussions on global citizenship and cross-cultural understanding</li></ul><p>The event reinforced our commitment to fostering an inclusive environment where all cultures are celebrated and respected. Special thanks to the Student Affairs Department and all volunteers who made this possible.</p><p>Over 500 attendees participated in various activities throughout the day, making it one of the most well-attended cultural events in recent years.</p>",
        image: "./img/culture.jpg",
        author: "Admin",
        featured: true
    },
    {
        id: "evt_002",
        title: "New Academic Year Registration Open",
        date: "2025-09-15",
        category: "Latest News",
        shortDescription: "Registration for the 2026 academic year is now open. Students can apply online or visit our campus for assistance with the enrollment process.",
        fullDescription: "<p>Lusaka South University College is now accepting applications for the 2026 academic year across all schools and programs.</p><h3>Important Dates:</h3><ul><li><strong>Application Opening:</strong> September 15, 2025</li><li><strong>Early Bird Deadline:</strong> November 30, 2025</li><li><strong>Final Deadline:</strong> January 15, 2026</li><li><strong>Classes Begin:</strong> February 2, 2026</li></ul><h3>How to Apply:</h3><ol><li>Visit our online application portal at <a href='https://srms.lsc.edu.zm/applications/undergraduate_application'>srms.lsc.edu.zm</a></li><li>Complete the application form with accurate information</li><li>Submit required documents (academic transcripts, ID copy, passport photo)</li><li>Pay the application fee through the online payment system</li></ol><p>For assistance, visit our admissions office or contact us at admissions@lsc.edu.zm</p>",
        image: "./img/enrollment.jpeg",
        author: "Admissions Office",
        featured: false
    },
    {
        id: "evt_003",
        title: "Solar Technology Excellence Award",
        date: "2025-03-10",
        category: "Latest News",
        shortDescription: "Our engineering students showcased exceptional quality work in Solar Technology, demonstrating the excellence of education at LSUC.",
        fullDescription: "<p>The School of Engineering is proud to announce that our Solar Technology students have received recognition for their outstanding projects at the National Technical Innovation Fair.</p><h3>Award-Winning Projects:</h3><ul><li><strong>Solar-Powered Water Pumping System:</strong> Designed for rural communities without grid access</li><li><strong>Portable Solar Charging Station:</strong> Mobile solution for emergency response situations</li><li><strong>Hybrid Solar-Wind System:</strong> Innovative approach to renewable energy generation</li></ul><p>These achievements reflect the quality of practical, hands-on training that LSUC provides to its students. The Solar Technology program combines theoretical knowledge with real-world application, preparing graduates for immediate contribution to Zambia's growing renewable energy sector.</p><p>Congratulations to all participating students and their dedicated instructors!</p>",
        image: "./img/students_solar tech.jpeg",
        author: "Engineering Department",
        featured: false
    },
    {
        id: "evt_004",
        title: "Graduation Ceremony 2025",
        date: "2025-12-15",
        category: "Upcoming Event",
        shortDescription: "Join us on December 15th, 2025, as we celebrate the achievements of our graduates from various programs. Registration starts at 8:00 AM.",
        fullDescription: "<p>You are cordially invited to attend the Lusaka South University College Graduation Ceremony 2025, celebrating the accomplishments of our graduating class.</p><h3>Event Details:</h3><ul><li><strong>Date:</strong> Monday, December 15, 2025</li><li><strong>Time:</strong> Registration from 8:00 AM, Ceremony begins at 10:00 AM</li><li><strong>Venue:</strong> LSUC Main Auditorium</li><li><strong>Dress Code:</strong> Formal attire / Academic regalia</li></ul><h3>Programme:</h3><ol><li>8:00 AM - Guest registration and seating</li><li>9:30 AM - Graduates assemble and processional</li><li>10:00 AM - National anthem and opening remarks</li><li>10:30 AM - Conferring of diplomas and certificates</li><li>12:30 PM - Graduation speech by guest of honour</li><li>1:30 PM - Vote of thanks and closing</li><li>2:00 PM - Refreshments and photo sessions</li></ol><p>Each graduate receives 4 complimentary tickets for family and friends. Additional tickets available upon request.</p>",
        image: "./img/school1.jpeg",
        author: "Administration",
        featured: true
    },
    {
        id: "evt_005",
        title: "October Intake Orientation Programme",
        date: "2025-10-21",
        category: "Upcoming Event",
        shortDescription: "Join us in welcoming the October intake as they embark on their academic journey. Campus tours and orientations will be conducted.",
        fullDescription: "<p>Welcome to the October 2025 Intake! We're excited to have you join the LSUC family.</p><h3>Orientation Schedule:</h3><ul><li><strong>8:00 AM - 9:00 AM:</strong> Registration and welcome packs collection</li><li><strong>9:00 AM - 10:30 AM:</strong> Welcome address by the Principal</li><li><strong>10:45 AM - 12:00 PM:</strong> School-specific briefings</li><li><strong>12:00 PM - 1:00 PM:</strong> Campus tour and facility familiarization</li><li><strong>1:00 PM - 2:00 PM:</strong> Lunch break</li><li><strong>2:00 PM - 3:30 PM:</strong> Student support services introduction</li><li><strong>3:30 PM - 4:30 PM:</strong> Q&A session with student leaders</li></ul><h3>What to Bring:</h3><ul><li>Admission letter</li><li>National ID or passport</li><li>Academic certificates (originals and copies)</li><li>Passport-sized photographs (4 copies)</li><li>Pen and notebook</li></ul><p>This orientation is mandatory for all new students. It's your opportunity to learn about college policies, meet your lecturers, and connect with fellow students.</p>",
        image: "./img/school2.jpeg",
        author: "Student Affairs",
        featured: false
    },
    {
        id: "evt_006",
        title: "Sports Week 2025",
        date: "2025-10-27",
        category: "Upcoming Event",
        shortDescription: "Inter-departmental sports competitions including football, Chess, Netball, and Drama & Poetry. Register with the sports department.",
        fullDescription: "<p>Get ready for the most exciting week of the year! LSUC Sports Week 2025 promises thrilling competitions and unforgettable moments.</p><h3>Events Schedule:</h3><ul><li><strong>Football Tournament:</strong> October 27-30, Main Field</li><li><strong>Netball Championship:</strong> October 27-29, Sports Complex</li><li><strong>Chess Competition:</strong> October 28, Library Hall</li><li><strong>Athletics:</strong> October 30, Running Track</li><li><strong>Drama & Poetry Slam:</strong> October 31, Auditorium</li></ul><h3>Registration:</h3><p>All interested students should register with the Sports Department before October 20th. Team sports require a minimum of 11 players with 4 substitutes.</p><h3>Prizes:</h3><ul><li>Winning teams receive trophies and medals</li><li>Best individual performers get special recognition</li><li>All participants receive certificates</li></ul><p>Let's showcase our talent and school spirit! Go LSUC!</p>",
        image: "./img/sports_complex.jpg",
        author: "Sports Department",
        featured: true
    },
    {
        id: "evt_007",
        title: "Direct Enrollment Officers Recruitment",
        date: "2025-09-20",
        category: "Job Vacancy",
        shortDescription: "We are glad to announce and congratulate the X30 DEO that made it for the vacancy opening from different districts.",
        fullDescription: "<p>Lusaka South College is pleased to announce the successful recruitment of 30 Direct Enrollment Officers (DEOs) from various districts across Zambia.</p><h3>Selected Candidates:</h3><p>The following candidates have been selected based on merit and performance in interviews conducted between September 1-15, 2025:</p><p><em>(Full list available at the Human Resources office)</em></p><h3>Next Steps:</h3><ol><li>Report to HR Department by September 25, 2025</li><li>Complete onboarding documentation</li><li>Attend mandatory training from September 26-28</li><li>Deployment to assigned districts begins October 1</li></ol><h3>Terms of Service:</h3><ul><li>Contract period: 2 years (renewable)</li><li>Competitive salary and allowances</li><li>Transportation provided</li><li>Performance-based incentives</li></ul><p>We congratulate all successful candidates and look forward to expanding our enrollment outreach through their efforts.</p>",
        image: "./img/school3.jpeg",
        author: "Human Resources",
        featured: false
    },
    {
        id: "evt_008",
        title: "Internship Opportunities - Final Year Students",
        date: "2025-09-05",
        category: "Job Vacancy",
        shortDescription: "Several companies have partnered with us to offer internship positions. Students in their final year are encouraged to apply through the career services office.",
        fullDescription: "<p>Exciting internship opportunities are now available for final year students through our industry partnerships.</p><h3>Partner Companies:</h3><ul><li><strong>ZESCO Limited:</strong> Electrical Engineering internships (10 positions)</li><li><strong>Zanaco Bank:</strong> Business Administration & Finance (8 positions)</li><li><strong>Multipurpose Co-operatives:</strong> Procurement & Supply Chain (5 positions)</li><li><strong>Tech Solutions Zambia:</strong> IT & Software Development (12 positions)</li><li><strong>BuildRight Construction:</strong> Civil Engineering (6 positions)</li></ul><h3>Eligibility Criteria:</h3><ul><li>Must be in final year of study</li><li>Minimum GPA of 3.0</li><li>Good standing with no disciplinary issues</li><li>Available for minimum 3-month placement</li></ul><h3>Application Process:</h3><ol><li>Visit Career Services Office, Room 204, Admin Building</li><li>Collect internship application form</li><li>Submit updated CV and academic transcript</li><li>Attach recommendation letter from your department</li><li>Deadline: September 25, 2025</li></ol><p>Successful candidates will be interviewed by respective companies in early October. This is an excellent opportunity to gain practical experience and potentially secure employment after graduation.</p>",
        image: "./img/computer_lab.jpg",
        author: "Career Services",
        featured: false
    },
    {
        id: "evt_009",
        title: "Corporate Training in Solar Installation",
        date: "2025-10-17",
        category: "Past Event",
        shortDescription: "Congratulations to our students who completed the Solar installation and maintenance Corporate training for the October intake.",
        fullDescription: "<p>The School of Engineering successfully concluded an intensive corporate training programme in Solar Installation and Maintenance.</p><h3>Training Overview:</h3><p>The two-week intensive programme equipped participants with practical skills in:</p><ul><li>Solar panel installation techniques</li><li>Inverter sizing and battery bank configuration</li><li>System design and load calculation</li><li>Troubleshooting and maintenance procedures</li><li>Safety protocols and quality standards</li></ul><h3>Certification:</h3><p>All 45 participants received Level 1 Certification in Solar Installation, recognized by the Technical Education, Vocational and Entrepreneurship Training Authority (TEVETA).</p><h3>Employment Prospects:</h3><p>Graduates of this programme are now qualified to work with solar installation companies or start their own businesses. Several participants have already received job offers from partner organizations.</p><p>The next intake is scheduled for January 2026. Interested individuals should contact the Continuing Professional Development (CPD) office.</p>",
        image: "./img/scienc_lab.jpg",
        author: "Engineering Department",
        featured: false
    },
    {
        id: "evt_010",
        title: "Campus Infrastructure Upgrade Completed",
        date: "2025-08-15",
        category: "Past Event",
        shortDescription: "The college has completed major infrastructure upgrades including renovated laboratories, expanded library facilities, and improved ICT infrastructure.",
        fullDescription: "<p>Lusaka South College is pleased to announce the completion of Phase 2 of our infrastructure development project.</p><h3>Completed Works:</h3><ul><li><strong>Science Laboratories:</strong> Complete renovation with modern equipment and safety features</li><li><strong>Computer Labs:</strong> 200 new desktop computers installed across three labs</li><li><strong>Library Expansion:</strong> Additional reading spaces and digital resource center</li><li><strong>Solar Power Installation:</strong> 50kW solar system reducing carbon footprint</li><li><strong>WiFi Network:</strong> Campus-wide high-speed internet coverage</li></ul><h3>Impact on Learning:</h3><p>These improvements significantly enhance the learning experience by providing students with access to modern facilities and technology. The upgraded labs accommodate larger class sizes and enable more hands-on practical sessions.</p><h3>Acknowledgments:</h3><p>The college management extends gratitude to the government, development partners, and alumni whose contributions made these improvements possible.</p><p>Phase 3, scheduled for 2026, will focus on hostel accommodation and sports facilities.</p>",
        image: "./img/library.jpg",
        author: "Infrastructure Development",
        featured: false
    }
];

// Initialize storage with sample data if empty
function initializeEventsData() {
    const existingData = localStorage.getItem(LSUC_EVENTS_STORAGE_KEY);
    
    if (!existingData || existingData === '[]') {
        localStorage.setItem(LSUC_EVENTS_STORAGE_KEY, JSON.stringify(SAMPLE_EVENTS));
        console.log('LSUC Events: Initialized with sample data');
        return SAMPLE_EVENTS;
    }
    
    return JSON.parse(existingData);
}

// ============================================
// CORE DATA ACCESS FUNCTIONS
// ============================================

// Get all events
function getEvents() {
    const events = localStorage.getItem(LSUC_EVENTS_STORAGE_KEY);
    return events ? JSON.parse(events) : [];
}

// Save all events
function saveEvents(events) {
    localStorage.setItem(LSUC_EVENTS_STORAGE_KEY, JSON.stringify(events));
    triggerDataUpdate();
}

// Get single event by ID
function getEventById(id) {
    const events = getEvents();
    return events.find(event => event.id === id);
}

// Get featured events (for homepage)
function getFeaturedEvents() {
    const events = getEvents();
    return events.filter(event => event.featured === true)
                 .sort((a, b) => new Date(b.date) - new Date(a.date));
}

// Get latest news (sorted by date, newest first)
function getLatestNews(limit = 6) {
    const events = getEvents();
    return events.filter(event => event.category === 'Latest News')
                 .sort((a, b) => new Date(b.date) - new Date(a.date))
                 .slice(0, limit);
}

// Get upcoming events (future dates, sorted by soonest first)
function getUpcomingEvents(limit = 6) {
    const events = getEvents();
    const today = new Date().toISOString().split('T')[0];
    
    return events.filter(event => 
        event.category === 'Upcoming Event' && 
        event.date >= today
    )
    .sort((a, b) => new Date(a.date) - new Date(b.date))
    .slice(0, limit);
}

// Get past events (past dates, sorted by most recent first)
function getPastEvents(page = 1, limit = 12) {
    const events = getEvents();
    const today = new Date().toISOString().split('T')[0];
    
    const pastEvents = events.filter(event => 
        event.category === 'Past Event'
    ).sort((a, b) => new Date(b.date) - new Date(a.date));
    
    const startIndex = (page - 1) * limit;
    const endIndex = startIndex + limit;
    
    return {
        events: pastEvents.slice(startIndex, endIndex),
        total: pastEvents.length,
        page: page,
        totalPages: Math.ceil(pastEvents.length / limit),
        hasMore: endIndex < pastEvents.length
    };
}

// Filter events by category
function filterByCategory(category) {
    const events = getEvents();
    
    if (category === 'all') {
        return events.sort((a, b) => new Date(b.date) - new Date(a.date));
    }
    
    return events.filter(event => event.category === category)
                 .sort((a, b) => new Date(b.date) - new Date(a.date));
}

// Search events
function searchEvents(query) {
    const events = getEvents();
    const searchTerm = query.toLowerCase().trim();
    
    return events.filter(event => 
        event.title.toLowerCase().includes(searchTerm) ||
        event.shortDescription.toLowerCase().includes(searchTerm) ||
        event.fullDescription.toLowerCase().includes(searchTerm) ||
        event.category.toLowerCase().includes(searchTerm) ||
        (event.author && event.author.toLowerCase().includes(searchTerm))
    ).sort((a, b) => new Date(b.date) - new Date(a.date));
}

// Sort events
function sortEvents(events, sortOrder = 'newest') {
    if (sortOrder === 'newest') {
        return [...events].sort((a, b) => new Date(b.date) - new Date(a.date));
    } else if (sortOrder === 'oldest') {
        return [...events].sort((a, b) => new Date(a.date) - new Date(b.date));
    }
    return events;
}

// ============================================
// CRUD OPERATIONS
// ============================================

// Generate unique ID
function generateId() {
    const timestamp = Date.now().toString(36);
    const randomPart = Math.random().toString(36).substring(2, 8);
    return `evt_${timestamp}_${randomPart}`;
}

// Add new event
function addEvent(eventData) {
    const events = getEvents();
    const newEvent = {
        ...eventData,
        id: generateId()
    };
    
    events.push(newEvent);
    saveEvents(events);
    return newEvent;
}

// Update existing event
function updateEvent(id, eventData) {
    const events = getEvents();
    const index = events.findIndex(event => event.id === id);
    
    if (index === -1) {
        throw new Error('Event not found');
    }
    
    events[index] = {
        ...events[index],
        ...eventData,
        id: id // Ensure ID doesn't change
    };
    
    saveEvents(events);
    return events[index];
}

// Delete event
function deleteEvent(id) {
    const events = getEvents();
    const filtered = events.filter(event => event.id !== id);
    
    if (filtered.length === events.length) {
        throw new Error('Event not found');
    }
    
    saveEvents(filtered);
    return true;
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

// Format date for display
function formatEventDate(dateString) {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

// Get days until event
function getDaysUntilEvent(dateString) {
    const today = new Date();
    const eventDate = new Date(dateString);
    const diffTime = eventDate - today;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays < 0) {
        return { passed: true, days: Math.abs(diffDays) };
    } else if (diffDays === 0) {
        return { today: true, days: 0 };
    } else {
        return { upcoming: true, days: diffDays };
    }
}

// Trigger custom event for UI updates
function triggerDataUpdate() {
    window.dispatchEvent(new CustomEvent('lsuc-events-updated'));
}

// ============================================
// ADMIN AUTHENTICATION
// ============================================

const ADMIN_PASSWORD = 'admin123';

// Verify admin password
function verifyAdmin(password) {
    if (password === ADMIN_PASSWORD) {
        localStorage.setItem(LSUC_ADMIN_AUTH_KEY, 'true');
        return true;
    }
    return false;
}

// Check if admin is authenticated
function isAdminAuthenticated() {
    return localStorage.getItem(LSUC_ADMIN_AUTH_KEY) === 'true';
}

// Logout admin
function logoutAdmin() {
    localStorage.removeItem(LSUC_ADMIN_AUTH_KEY);
}

// ============================================
// EXPORT PUBLIC API
// ============================================

window.LSUCEventsManager = {
    // Initialization
    initialize: initializeEventsData,
    
    // Data Access
    getEvents,
    getEventById,
    getFeaturedEvents,
    getLatestNews,
    getUpcomingEvents,
    getPastEvents,
    filterByCategory,
    searchEvents,
    sortEvents,
    
    // CRUD Operations
    addEvent,
    updateEvent,
    deleteEvent,
    
    // Utilities
    formatEventDate,
    getDaysUntilEvent,
    generateId,
    
    // Admin
    verifyAdmin,
    isAdminAuthenticated,
    logoutAdmin
};

// Auto-initialize on script load
initializeEventsData();

console.log('LSUC News Events Manager loaded successfully');

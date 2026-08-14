<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\College;
use App\Models\Department;
use App\Models\User;
use App\Models\ActivitySubmission;
use App\Models\ActivityMedia;
use App\Models\ApprovalLog;
use App\Models\PublishedPost;
use App\Models\PortalNotification;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Constituent Colleges
        $collegesData = [
            [
                'code' => 'VMKVEC',
                'name' => "Vinayaka Mission's Kirupananda Variar Engineering College",
                'location' => 'Salem, Tamil Nadu',
                'dean_name' => 'Dr. A. Nagappan',
                'contact_email' => 'principal.vmkvec@vmrfdu.edu.in',
                'website' => 'https://www.vmkvec.ac.in',
            ],
            [
                'code' => 'AVIT',
                'name' => 'Aarupadai Veedu Institute of Technology',
                'location' => 'Paiyanoor, Chennai',
                'dean_name' => 'Dr. G. Selvakumar',
                'contact_email' => 'principal@avit.ac.in',
                'website' => 'https://www.avit.ac.in',
            ],
            [
                'code' => 'VMKVMC',
                'name' => "Vinayaka Mission's Kirupananda Variar Medical College & Hospital",
                'location' => 'Salem, Tamil Nadu',
                'dean_name' => 'Dr. K. Ezhilvanan',
                'contact_email' => 'dean.vmkvmc@vmrfdu.edu.in',
                'website' => 'https://www.vmkvmc.edu.in',
            ],
            [
                'code' => 'AVMC',
                'name' => 'Aarupadai Veedu Medical College & Hospital',
                'location' => 'Kirumampakkam, Puducherry',
                'dean_name' => 'Dr. Rakesh Sehgal',
                'contact_email' => 'dean@avmc.edu.in',
                'website' => 'https://avmc.edu.in',
            ],
            [
                'code' => 'VMSDC',
                'name' => "Vinayaka Mission's Sankarachariyar Dental College",
                'location' => 'Salem, Tamil Nadu',
                'dean_name' => 'Dr. J. Kirthiga',
                'contact_email' => 'principal.vmsdc@vmrfdu.edu.in',
                'website' => 'https://www.vmsdc.edu.in',
            ],
            [
                'code' => 'VMCON',
                'name' => "Vinayaka Mission's College of Nursing",
                'location' => 'Puducherry',
                'dean_name' => 'Dr. G. Muthamilselvi',
                'contact_email' => 'principal.vmcon@vmrfdu.edu.in',
                'website' => 'https://www.vmconpdy.edu.in',
            ],
            [
                'code' => 'VMCP',
                'name' => "Vinayaka Mission's College of Pharmacy",
                'location' => 'Salem, Tamil Nadu',
                'dean_name' => 'Dr. B. Jaykar',
                'contact_email' => 'principal.vmcp@vmrfdu.edu.in',
                'website' => 'https://www.vmcp.edu.in',
            ]
        ];

        $colleges = [];
        foreach ($collegesData as $data) {
            $colleges[$data['code']] = College::create($data);
        }

        $this->call(ConstituentCollegesSeeder::class);

        // 2. Create Departments
        $depts = [
            'VMKVEC' => ['Computer Science & Engineering', 'Biotechnology', 'Mechanical Engineering', 'Artificial Intelligence & Data Science'],
            'AVIT' => ['Bio-Medical Engineering', 'Electronics & Communication', 'Civil Engineering', 'Computer Science & Business Systems'],
            'VMKVMC' => ['General Medicine', 'Cardiology', 'Pediatrics', 'Community Medicine'],
            'AVMC' => ['Orthopedics', 'Pathology', 'Anesthesiology', 'Radiology'],
            'VMSDC' => ['Oral Surgery', 'Orthodontics', 'Periodontology'],
            'VMCON' => ['Medical Surgical Nursing', 'Community Health Nursing'],
            'VMCP' => ['Pharmaceutics', 'Pharmacology', 'Pharmaceutical Chemistry']
        ];

        $departments = [];
        foreach ($depts as $colCode => $deptList) {
            foreach ($deptList as $dName) {
                $departments[$colCode][] = Department::create([
                    'college_id' => $colleges[$colCode]->id,
                    'code' => strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $dName), 0, 4)),
                    'name' => $dName,
                ]);
            }
        }

        // 3. Create Users across all roles
        $defaultPassword = Hash::make('password');

        // Super Admin
        $superAdmin = User::create([
            'name' => 'Dr. S. K. Ramesh (Super Admin)',
            'email' => 'superadmin@vmrfdu.edu.in',
            'password' => $defaultPassword,
            'role' => 'super_admin',
            'designation' => 'System Director',
            'phone' => '+91 9840011223',
        ]);

        // Registrar (Initial Verifier)
        $registrar = User::create([
            'name' => 'Dr. B. Jayakar (Registrar)',
            'email' => 'registrar@vmrfdu.edu.in',
            'password' => $defaultPassword,
            'role' => 'registrar',
            'designation' => 'Registrar, VMRFDU',
            'phone' => '+91 9840011224',
        ]);

        // Office of the Registrar (Final Approval Authority)
        $officeRegistrar = User::create([
            'name' => 'Office of the Registrar Desk',
            'email' => 'officeregistrar@vmrfdu.edu.in',
            'password' => $defaultPassword,
            'role' => 'office_registrar',
            'designation' => 'Director, Office of the Registrar',
            'phone' => '+91 9840011235',
        ]);

        // Vice Chancellor
        $vc = User::create([
            'name' => 'Prof. Dr. P. K. Sudhir (Vice Chancellor)',
            'email' => 'vc@vmrfdu.edu.in',
            'password' => $defaultPassword,
            'role' => 'vc',
            'designation' => 'Vice Chancellor',
            'phone' => '+91 9840011225',
        ]);

        // Pro VC
        $provc = User::create([
            'name' => 'Dr. J. Sabarinathan (Pro VC)',
            'email' => 'provc@vmrfdu.edu.in',
            'password' => $defaultPassword,
            'role' => 'pro_vc',
            'designation' => 'Pro Vice Chancellor (Academics)',
            'phone' => '+91 9840011226',
        ]);

        // Media Editor
        $mediaEditor = User::create([
            'name' => 'Karthik N. (Lead Media Editor)',
            'email' => 'editor@vmrfdu.edu.in',
            'password' => $defaultPassword,
            'role' => 'media_editor',
            'designation' => 'Head of Digital Media & PR',
            'phone' => '+91 9840011227',
        ]);

        // Viewer / Reports Access
        $viewer = User::create([
            'name' => 'Anitha V. (IQAC Officer)',
            'email' => 'reports@vmrfdu.edu.in',
            'password' => $defaultPassword,
            'role' => 'viewer',
            'designation' => 'IQAC Statistical Coordinator',
            'phone' => '+91 9840011228',
        ]);

        // College DEOs
        $deoVmkvec = User::create([
            'name' => 'Muthukumar S. (DEO VMKVEC)',
            'email' => 'deo.vmkvec@vmrfdu.edu.in',
            'password' => $defaultPassword,
            'role' => 'college_admin',
            'college_id' => $colleges['VMKVEC']->id,
            'department_id' => $departments['VMKVEC'][0]->id,
            'designation' => 'Public Relations Coordinator',
            'phone' => '+91 9840011301',
        ]);

        $deoAvit = User::create([
            'name' => 'Deepa R. (DEO AVIT)',
            'email' => 'deo.avit@vmrfdu.edu.in',
            'password' => $defaultPassword,
            'role' => 'college_admin',
            'college_id' => $colleges['AVIT']->id,
            'department_id' => $departments['AVIT'][0]->id,
            'designation' => 'Media Liaison Officer',
            'phone' => '+91 9840011302',
        ]);

        $deoVmkvmc = User::create([
            'name' => 'Rajesh K. (DEO VMKVMC)',
            'email' => 'deo.vmkvmc@vmrfdu.edu.in',
            'password' => $defaultPassword,
            'role' => 'college_admin',
            'college_id' => $colleges['VMKVMC']->id,
            'department_id' => $departments['VMKVMC'][0]->id,
            'designation' => 'Hospital Media Officer',
            'phone' => '+91 9840011303',
        ]);

        // 4. Sample Activity Submissions (Commented / disabled by default for clean production/testing slate)
        /*
        $sub1 = ActivitySubmission::create([
            'reference_id' => 'VMRF-2026-0001',
            'user_id' => $deoVmkvec->id,
            'college_id' => $colleges['VMKVEC']->id,
            'department_id' => $departments['VMKVEC'][0]->id,
            'title' => 'International Conference on Deep Learning & Autonomous Systems (ICDLAS 2026)',
            'description' => "The Department of Computer Science & Engineering hosted a 2-day International Conference featuring keynotes from AI scientists from NUS Singapore and IIT Madras. Over 350 researchers participated, presenting 80+ peer-reviewed papers on neural networks and robotics.",
            'activity_date' => Carbon::now()->subDays(3)->format('Y-m-d'),
            'event_category' => 'Conference',
            'tags' => 'AI, Conference, VMKVEC, Robotics, Research',
            'location' => 'Main Auditorium, VMKVEC Salem',
            'faculty_coordinator_name' => 'Dr. R. Sasikumar',
            'faculty_coordinator_email' => 'sasikumar.cse@vmkvec.ac.in',
            'faculty_coordinator_phone' => '+91 9443211223',
            'social_media_caption' => "Exploring the future of Artificial Intelligence! ðŸ¤– VMKVEC successfully hosted ICDLAS 2026 with 350+ researchers and global keynote speakers from NUS & IIT Madras. Proud to foster cutting-edge autonomous technology innovation! ðŸŒŸ #VMRFDU #VMKVEC #ArtificialIntelligence #Conference2026",
            'status' => 'published',
            'current_stage' => 'Published Live',
            'admin_remarks' => 'Approved and published on all major channels.',
        ]);

        // Add Media for sub1
        ActivityMedia::create([
            'activity_submission_id' => $sub1->id,
            'file_path' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=1200&q=80',
            'file_name' => 'icdlas_keynote_hall.jpg',
            'file_type' => 'image',
            'file_size' => 452000,
            'mime_type' => 'image/jpeg',
            'is_primary' => true,
            'dimensions' => '1200x800',
        ]);
        ActivityMedia::create([
            'activity_submission_id' => $sub1->id,
            'file_path' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1200&q=80',
            'file_name' => 'audience_interaction.jpg',
            'file_type' => 'image',
            'file_size' => 389000,
            'mime_type' => 'image/jpeg',
            'is_primary' => false,
            'dimensions' => '1200x800',
        ]);

        ApprovalLog::create([
            'activity_submission_id' => $sub1->id,
            'user_id' => $deoVmkvec->id,
            'action' => 'submitted',
            'from_status' => 'draft',
            'to_status' => 'verification_pending',
            'remarks' => 'Initial submission by DEO.',
        ]);
        ApprovalLog::create([
            'activity_submission_id' => $sub1->id,
            'user_id' => $registrar->id,
            'action' => 'approved',
            'from_status' => 'verification_pending',
            'to_status' => 'media_review',
            'remarks' => 'Verified high-level academic event. Forwarded to Media Editor.',
        ]);
        ApprovalLog::create([
            'activity_submission_id' => $sub1->id,
            'user_id' => $mediaEditor->id,
            'action' => 'published',
            'from_status' => 'media_review',
            'to_status' => 'published',
            'remarks' => 'Images optimized and published across official Instagram and LinkedIn handles.',
        ]);

        PublishedPost::create([
            'activity_submission_id' => $sub1->id,
            'media_editor_id' => $mediaEditor->id,
            'platforms' => ['Instagram', 'LinkedIn', 'Twitter/X'],
            'published_caption' => $sub1->social_media_caption,
            'published_at' => Carbon::now()->subDay(),
            'post_url_instagram' => 'https://instagram.com/vmrfdu_official/post/example1',
            'post_url_linkedin' => 'https://linkedin.com/school/vmrfdu/posts/example1',
        ]);

        // Sub 2: Pending Media Editor Review
        $sub2 = ActivitySubmission::create([
            'reference_id' => 'VMRF-2026-0002',
            'user_id' => $deoAvit->id,
            'college_id' => $colleges['AVIT']->id,
            'department_id' => $departments['AVIT'][0]->id,
            'title' => 'National Level Hackathon - AVIT HackFest 2026',
            'description' => "Over 120 student teams from across India competed in a 36-hour non-stop hackathon focused on Smart Healthcare and Green Energy solutions. Total prize money of Rs. 3 Lakhs was awarded by industry leaders.",
            'activity_date' => Carbon::now()->subDays(1)->format('Y-m-d'),
            'event_category' => 'Workshop / Hackathon',
            'tags' => 'Hackathon, AVIT, Coding, Innovation, Students',
            'location' => 'AVIT Campus, Paiyanoor Chennai',
            'faculty_coordinator_name' => 'Prof. S. Prakash',
            'faculty_coordinator_email' => 'prakash@avit.ac.in',
            'faculty_coordinator_phone' => '+91 9884433221',
            'social_media_caption' => "Code, Create, Conquer! ðŸ’»âš¡ AVIT HackFest 2026 brought together 120+ teams nationwide for a 36-hour innovation marathon solving critical healthcare and sustainability challenges! Congratulations to our brilliant winners! ðŸ† #AVITHackFest #VMRFDU #YoungInnovators #TechLife",
            'status' => 'media_review',
            'current_stage' => 'Media Editor Review',
            'admin_remarks' => 'Approved by VC office for social media publishing.',
        ]);
        ActivityMedia::create([
            'activity_submission_id' => $sub2->id,
            'file_path' => 'https://images.unsplash.com/photo-1531482615713-2afd69097998?auto=format&fit=crop&w=1200&q=80',
            'file_name' => 'hackathon_coding_night.jpg',
            'file_type' => 'image',
            'file_size' => 512000,
            'mime_type' => 'image/jpeg',
            'is_primary' => true,
            'dimensions' => '1200x800',
        ]);
        ApprovalLog::create([
            'activity_submission_id' => $sub2->id,
            'user_id' => $deoAvit->id,
            'action' => 'submitted',
            'from_status' => 'draft',
            'to_status' => 'verification_pending',
            'remarks' => 'Submitted with high-res event photography.',
        ]);
        ApprovalLog::create([
            'activity_submission_id' => $sub2->id,
            'user_id' => $vc->id,
            'action' => 'approved',
            'from_status' => 'verification_pending',
            'to_status' => 'media_review',
            'remarks' => 'Great student turnout. Approved for immediate posting.',
        ]);

        // Sub 3: Pending Approval from Registrar/VC
        $sub3 = ActivitySubmission::create([
            'reference_id' => 'VMRF-2026-0003',
            'user_id' => $deoVmkvmc->id,
            'college_id' => $colleges['VMKVMC']->id,
            'department_id' => $departments['VMKVMC'][1]->id,
            'title' => 'Free Advanced Cardiology & Health Screening Camp',
            'description' => "Department of Cardiology organized a free screening camp for over 600 rural residents around Salem district, providing free ECG, Echo checks, and cardiac consultations by expert doctors.",
            'activity_date' => Carbon::now()->format('Y-m-d'),
            'event_category' => 'Extension/CSR',
            'tags' => 'Healthcare, Cardiology, Medical Camp, VMKVMC, CSR',
            'location' => 'VMKV Medical College Hospital Premises',
            'faculty_coordinator_name' => 'Dr. M. Senthilvelan',
            'faculty_coordinator_email' => 'cardio@vmkvmc.edu.in',
            'faculty_coordinator_phone' => '+91 9789911223',
            'social_media_caption' => "Serving our community with care â¤ï¸ VMKVMC Cardiology Department conducted a massive Free Cardiology Screening Camp for 600+ rural citizens in Salem. Health for all remains our commitment! ðŸ¥âœ¨ #VMKVMC #VMRFDU #HealthcareCamp #CommunityCare",
            'status' => 'approval_pending',
            'current_stage' => 'Registrar / VC Office',
        ]);
        ActivityMedia::create([
            'activity_submission_id' => $sub3->id,
            'file_path' => 'https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&w=1200&q=80',
            'file_name' => 'cardiology_screening_doctors.jpg',
            'file_type' => 'image',
            'file_size' => 430000,
            'mime_type' => 'image/jpeg',
            'is_primary' => true,
            'dimensions' => '1200x800',
        ]);

        // Sub 4: Verification Pending
        $sub4 = ActivitySubmission::create([
            'reference_id' => 'VMRF-2026-0004',
            'user_id' => $deoVmkvec->id,
            'college_id' => $colleges['VMKVEC']->id,
            'department_id' => $departments['VMKVEC'][1]->id,
            'title' => 'DST-SERB Sponsored Research Grant of Rs. 45 Lakhs Awarded',
            'description' => "Dr. P. Gomathi (Head of Biotech) secured a prestigious research grant from DST-SERB to develop eco-friendly bio-degradable polymer implants for bone regeneration.",
            'activity_date' => Carbon::now()->addDays(1)->format('Y-m-d'),
            'event_category' => 'Award/Achievement',
            'tags' => 'Research, Grant, DST, Biotechnology, VMKVEC',
            'location' => 'Biotech Research Center, VMKVEC',
            'faculty_coordinator_name' => 'Dr. P. Gomathi',
            'faculty_coordinator_email' => 'gomathi.biotech@vmkvec.ac.in',
            'faculty_coordinator_phone' => '+91 9443388776',
            'social_media_caption' => "Pioneering Research at VMRFDU! ðŸ”¬âœ¨ Congratulations to Dr. P. Gomathi & Department of Biotechnology (VMKVEC) on securing a major Rs. 45 Lakhs research grant from DST-SERB for revolutionary bone implant technology! #ResearchExcellence #VMRFDU #VMKVEC #Innovation",
            'status' => 'verification_pending',
            'current_stage' => 'Registrar Verification',
        ]);
        ActivityMedia::create([
            'activity_submission_id' => $sub4->id,
            'file_path' => 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?auto=format&fit=crop&w=1200&q=80',
            'file_name' => 'biotech_lab_research.jpg',
            'file_type' => 'image',
            'file_size' => 390000,
            'mime_type' => 'image/jpeg',
            'is_primary' => true,
            'dimensions' => '1200x800',
        ]);

        // Sub 5: Sent Back / Query (Discuss)
        $sub5 = ActivitySubmission::create([
            'reference_id' => 'VMRF-2026-0005',
            'user_id' => $deoAvit->id,
            'college_id' => $colleges['AVIT']->id,
            'department_id' => $departments['AVIT'][1]->id,
            'title' => 'Guest Lecture on 6G Communication & IoT Architecture',
            'description' => "Guest lecture conducted for final year ECE students.",
            'activity_date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'event_category' => 'Guest Lecture',
            'tags' => 'ECE, Guest Lecture, 6G, AVIT',
            'location' => 'Seminar Hall 2, AVIT',
            'faculty_coordinator_name' => 'Dr. L. Kanan',
            'faculty_coordinator_email' => 'kanan@avit.ac.in',
            'faculty_coordinator_phone' => '+91 9840055443',
            'social_media_caption' => "Stepping into 6G & IoT technology at AVIT! Great session for ECE students.",
            'status' => 'discuss',
            'current_stage' => 'Returned for Correction (Editable Once)',
            'admin_remarks' => 'Please provide the full name, designation, and company/university of the Guest Speaker, and attach at least two high-resolution action photos.',
        ]);

        // Sub 6: Edited by Media Editor -> Pending Office of the Registrar Approval
        $sub6 = ActivitySubmission::create([
            'reference_id' => 'VMRF-2026-0006',
            'user_id' => $deoVmkvmc->id,
            'college_id' => $colleges['VMKVMC']->id,
            'department_id' => $departments['VMKVMC'][0]->id,
            'title' => 'National Symposium on Robotic Surgery & AI Diagnostics',
            'description' => "VMKV Medical College hosted a hands-on robotic surgery workshop with leading AI surgical systems. Over 200 surgeons and medical students practiced precision robotic micro-suturing techniques.",
            'activity_date' => Carbon::now()->format('Y-m-d'),
            'event_category' => 'Symposium',
            'tags' => 'RoboticSurgery, AI, VMKVMC, MedicalInnovation, Healthcare',
            'location' => 'Advanced Simulation Lab, VMKVMC Salem',
            'faculty_coordinator_name' => 'Dr. V. Natarajan',
            'faculty_coordinator_email' => 'surgery@vmkvmc.edu.in',
            'faculty_coordinator_phone' => '+91 9443311889',
            'social_media_caption' => "Revolutionizing modern healthcare! ðŸ¤–ðŸ¥ VMKVMC successfully conducted the National Symposium on Robotic Surgery & AI Diagnostics, empowering 200+ surgeons with hands-on precision robotic training. Future of surgery is here! âœ¨ #VMKVMC #RoboticSurgery #MedicalInnovation #VMRFDU",
            'status' => 'registrar_approval',
            'current_stage' => 'Office of the Registrar Approval',
            'admin_remarks' => 'Edited and prepared by Media Editor. Submitted for final Registrar approval.',
        ]);
        ActivityMedia::create([
            'activity_submission_id' => $sub6->id,
            'file_path' => 'https://images.unsplash.com/photo-1581594693702-fbdc51b2763b?auto=format&fit=crop&w=1200&q=80',
            'file_name' => 'robotic_surgery_sim.jpg',
            'file_type' => 'image',
            'file_size' => 480000,
            'mime_type' => 'image/jpeg',
            'is_primary' => true,
            'dimensions' => '1200x800',
        ]);
        ApprovalLog::create([
            'activity_submission_id' => $sub6->id,
            'user_id' => $mediaEditor->id,
            'action' => 'submitted to Registrar',
            'from_status' => 'media_review',
            'to_status' => 'registrar_approval',
            'remarks' => 'Caption refined and photo color-graded. Submitted for formal Registrar verification.',
        ]);

        // Sub 7: Approved by Registrar -> Ready for Publication
        $sub7 = ActivitySubmission::create([
            'reference_id' => 'VMRF-2026-0007',
            'user_id' => $deoAvit->id,
            'college_id' => $colleges['AVIT']->id,
            'department_id' => $departments['AVIT'][2]->id,
            'title' => 'Green Campus Solar Micro-Grid Commissioning Ceremony',
            'description' => "AVIT commissioned a new 500kW rooftop solar micro-grid capable of powering 70% of academic blocks with clean renewable energy.",
            'activity_date' => Carbon::now()->subDay()->format('Y-m-d'),
            'event_category' => 'Inauguration / Ceremony',
            'tags' => 'SolarEnergy, Sustainability, GreenCampus, AVIT, Renewable',
            'location' => 'Main Academic Block Roof, AVIT Paiyanoor',
            'faculty_coordinator_name' => 'Dr. T. Subash',
            'faculty_coordinator_email' => 'subash.eee@avit.ac.in',
            'faculty_coordinator_phone' => '+91 9840099887',
            'social_media_caption' => "Powering a cleaner tomorrow! â˜€ï¸ðŸŒ± AVIT proudly commissions its new 500kW Solar Micro-Grid, transitioning 70% of our academic infrastructure to clean, green renewable energy! #GreenCampus #AVIT #VMRFDU #Sustainability #CleanEnergy",
            'status' => 'ready_to_publish',
            'current_stage' => 'Approved by Registrar â€” Ready for Publication',
            'admin_remarks' => 'Formal approval granted by Office of the Registrar. Authorized for live broadcast.',
        ]);
        ActivityMedia::create([
            'activity_submission_id' => $sub7->id,
            'file_path' => 'https://images.unsplash.com/photo-1509391365360-bbcbde3c87dc?auto=format&fit=crop&w=1200&q=80',
            'file_name' => 'solar_panels_campus.jpg',
            'file_type' => 'image',
            'file_size' => 465000,
            'mime_type' => 'image/jpeg',
            'is_primary' => true,
            'dimensions' => '1200x800',
        ]);
        ApprovalLog::create([
            'activity_submission_id' => $sub7->id,
            'user_id' => $registrar->id,
            'action' => 'approved by Office of the Registrar',
            'from_status' => 'registrar_approval',
            'to_status' => 'ready_to_publish',
            'remarks' => 'Formal approval granted by Office of the Registrar. Authorized for live broadcast.',
        ]);

        // Create Notifications & Sample Email Logs
        PortalNotification::create([
            'user_id' => $registrar->id,
            'title' => 'Media Package Pending Registrar Review',
            'message' => 'Activity VMRF-2026-0006 (National Symposium on Robotic Surgery) edited by Media Desk awaits final Registrar approval.',
            'type' => 'info',
            'action_url' => '/approvals',
        ]);
        PortalNotification::create([
            'user_id' => $registrar->id,
            'title' => 'New Submission Pending Review',
            'message' => 'Activity VMRF-2026-0003 (Free Advanced Cardiology Camp) requires your verification.',
            'type' => 'warning',
            'action_url' => '/approvals',
        ]);
        PortalNotification::create([
            'user_id' => $mediaEditor->id,
            'title' => 'Activity Approved for Media Publishing',
            'message' => 'Activity VMRF-2026-0002 (National Level Hackathon) has been approved and is ready for social media scheduling.',
            'type' => 'success',
            'action_url' => '/media-editor',
        ]);
        PortalNotification::create([
            'user_id' => $deoAvit->id,
            'title' => 'Submission Sent Back with Query',
            'message' => 'Activity VMRF-2026-0005 was returned by Registrar: "Please provide speaker details and high-res photos."',
            'type' => 'alert',
            'action_url' => '/submissions',
        ]);

        \App\Models\EmailLog::create([
            'recipient_user_id' => $registrar->id,
            'recipient_email' => $registrar->email,
            'subject' => '[VMRFDU Portal] New Activity Submitted: VMRF-2026-0003',
            'stage' => 'SUBMITTED',
            'body_html' => '<div style="font-family:sans-serif;padding:20px;"><h3>VMRFDU Official Email Notification</h3><p>Dear Dr. B. Jayakar (Registrar),</p><p>Activity VMRF-2026-0003 (Free Advanced Cardiology Camp) has been submitted by VMKVMC and is awaiting your verification.</p><hr><a href="/approvals" style="background:#4f46e5;color:#fff;padding:10px 20px;text-decoration:none;border-radius:6px;">Review Submission</a></div>',
            'status' => 'sent',
        ]);
        \App\Models\EmailLog::create([
            'recipient_user_id' => $mediaEditor->id,
            'recipient_email' => $mediaEditor->email,
            'subject' => '[VMRFDU Portal] APPROVED & FORWARDED TO MEDIA DESK: VMRF-2026-0002',
            'stage' => 'APPROVED',
            'body_html' => '<div style="font-family:sans-serif;padding:20px;"><h3>VMRFDU Official Email Notification</h3><p>Dear Karthik N. (Lead Media Editor),</p><p>Activity VMRF-2026-0002 (National Level Hackathon) has been approved by the Vice Chancellor office. It is now forwarded to your desk for cropping and media scheduling.</p><hr><a href="/media-editor" style="background:#10b981;color:#fff;padding:10px 20px;text-decoration:none;border-radius:6px;">Launch Media Desk</a></div>',
            'status' => 'sent',
        ]);
        */
    }
}

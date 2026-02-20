<?php

namespace Database\Seeders;

use App\Models\CaseStudy;
use App\Models\Client;
use App\Models\CompanyProfile;
use App\Models\Industry;
use App\Models\MediaFile;
use App\Models\ProcessStep;
use App\Models\Service;
use App\Models\TeamMember;
use App\Models\TechCategory;
use App\Models\TechItem;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Generate a translatable array with fallbacks.
     */
    private function trans(
        string $en,
        string $ar = '',
        string $fr = '',
        string $es = '',
        string $tr = '',
        string $it = '',
        string $ru = '',
        string $zh = '',
        string $hi = '',
        string $bn = '',
        string $ur = '',
        string $pt = '',
        string $id = '',
        string $pcm = ''
    ): array {
        return array_filter([
            'en' => $en,
            'ar' => $ar ?: $en,
            'fr' => $fr ?: $en,
            'es' => $es ?: $en,
            'tr' => $tr ?: $en,
            'it' => $it ?: $en,
            'ru' => $ru ?: $en,
            'zh' => $zh ?: $en,
            'hi' => $hi ?: $en,
            'bn' => $bn ?: $en,
            'ur' => $ur ?: $en,
            'pt' => $pt ?: $en,
            'id' => $id ?: $en,
            'pcm' => $pcm ?: $en,
        ], fn($v) => $v !== '');
    }

    /**
     * Generate translatable slugs.
     */
    private function slugs(string $en, string $ar = ''): array
    {
        return $this->trans(
            Str::slug($en),
            $ar ? Str::slug($ar) : Str::slug($en),
        );
    }

    public function run(): void
    {
        // =====================================================================
        // 1. Admin User
        // =====================================================================
        User::create([
            'name' => 'Admin',
            'email' => 'admin@aroundsolutions.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // =====================================================================
        // 2. Company Profile
        // =====================================================================
        CompanyProfile::create([
            'company_name' => $this->trans(
                'Around Solutions',
                'حلول أراوند',
                'Around Solutions',
                'Around Solutions',
                'Around Solutions',
                'Around Solutions',
                'Around Solutions',
                'Around Solutions',
            ),
            'tagline' => $this->trans(
                'Building Digital Solutions That Make a Difference',
                'نبني حلولاً رقمية تُحدث فرقاً',
                'Des solutions numeriques qui font la difference',
                'Soluciones digitales que marcan la diferencia',
            ),
            'about' => $this->trans(
                "Around Solutions is a leading software development company headquartered in Riyadh, Saudi Arabia. We specialize in building high-quality web applications, mobile apps, and enterprise systems that drive business growth.\n\nFounded with a vision to bridge the gap between business needs and technology, we have been delivering innovative digital solutions to clients across the Middle East, Europe, and beyond.\n\nOur team of experienced developers, designers, and project managers work collaboratively using agile methodologies to ensure every project exceeds expectations and delivers measurable results.",
                "حلول أراوند هي شركة رائدة في تطوير البرمجيات يقع مقرها الرئيسي في الرياض، المملكة العربية السعودية. نتخصص في بناء تطبيقات الويب عالية الجودة وتطبيقات الهواتف الذكية والأنظمة المؤسسية التي تدفع نمو الأعمال.\n\nتأسست برؤية لسد الفجوة بين احتياجات الأعمال والتكنولوجيا، وقد قدمنا حلولاً رقمية مبتكرة للعملاء في جميع أنحاء الشرق الأوسط وأوروبا وخارجها.\n\nيعمل فريقنا من المطورين والمصممين ومديري المشاريع ذوي الخبرة بشكل تعاوني باستخدام منهجيات رشيقة لضمان تجاوز كل مشروع للتوقعات وتقديم نتائج قابلة للقياس.",
            ),
            'mission' => $this->trans(
                'To empower businesses with innovative technology solutions that drive sustainable growth, operational efficiency, and competitive advantage in an increasingly digital world.',
                'تمكين الشركات بحلول تقنية مبتكرة تدفع النمو المستدام والكفاءة التشغيلية والميزة التنافسية في عالم رقمي متزايد.',
            ),
            'vision' => $this->trans(
                'To be the most trusted technology partner for enterprises across the Middle East and beyond, recognized for excellence, innovation, and lasting impact.',
                'أن نكون الشريك التقني الأكثر ثقة للمؤسسات في الشرق الأوسط وخارجه، معترفاً بنا للتميز والابتكار والتأثير الدائم.',
            ),
            'values' => $this->trans(
                "Innovation - We constantly explore emerging technologies and creative approaches\nQuality - We never compromise on code quality, testing, or user experience\nTransparency - Open and honest communication with our clients at every stage\nPartnership - We treat every client as a long-term strategic partner\nExcellence - We strive for excellence in everything we deliver",
                "الابتكار - نستكشف باستمرار التقنيات الناشئة والأساليب الإبداعية\nالجودة - لا نتنازل أبداً عن جودة الكود والاختبار وتجربة المستخدم\nالشفافية - تواصل مفتوح وصادق مع عملائنا في كل مرحلة\nالشراكة - نعامل كل عميل كشريك استراتيجي طويل الأمد\nالتميز - نسعى للتميز في كل ما نقدمه",
            ),
            'email' => 'info@aroundsolutions.com',
            'phone' => '+966 11 234 5678',
            'address' => $this->trans(
                'King Fahd Road, Al Olaya District, Riyadh 12211, Saudi Arabia',
                'طريق الملك فهد، حي العليا، الرياض 12211، المملكة العربية السعودية',
            ),
            'whatsapp_link' => 'https://wa.me/966112345678',
            'calendly_link' => 'https://calendly.com/aroundsolutions/consultation',
            'facebook' => 'https://facebook.com/aroundsolutions',
            'twitter' => 'https://x.com/aroundsolutions',
            'linkedin' => 'https://linkedin.com/company/aroundsolutions',
            'instagram' => 'https://instagram.com/aroundsolutions',
            'github' => 'https://github.com/aroundsolutions',
        ]);

        // =====================================================================
        // 3. Services
        // =====================================================================
        $services = [];
        $serviceData = [
            [
                'title' => $this->trans('Custom Web Application Development', 'تطوير تطبيقات الويب المخصصة', 'Developpement d\'applications web sur mesure', 'Desarrollo de aplicaciones web personalizadas'),
                'slug' => $this->slugs('custom-web-application-development'),
                'description' => $this->trans(
                    'We build scalable, high-performance web applications using modern frameworks like Laravel, Vue.js, and React. From complex SaaS platforms to internal business tools, our full-stack team delivers solutions that grow with your business.',
                    'نبني تطبيقات ويب قابلة للتوسع وعالية الأداء باستخدام أطر عمل حديثة مثل Laravel و Vue.js و React. من منصات SaaS المعقدة إلى أدوات الأعمال الداخلية، يقدم فريقنا الشامل حلولاً تنمو مع عملك.',
                ),
                'deliverables' => $this->trans(
                    "Full-stack web application development\nRESTful API design and implementation\nDatabase architecture and optimization\nThird-party integrations and payment gateways\nPerformance optimization and caching strategies",
                    "تطوير تطبيقات ويب شاملة\nتصميم وتنفيذ واجهات برمجة RESTful\nهندسة قواعد البيانات وتحسينها\nتكاملات الطرف الثالث وبوابات الدفع\nتحسين الأداء واستراتيجيات التخزين المؤقت",
                ),
                'icon' => 'globe',
            ],
            [
                'title' => $this->trans('Mobile Application Development', 'تطوير تطبيقات الهواتف الذكية', 'Developpement d\'applications mobiles', 'Desarrollo de aplicaciones moviles'),
                'slug' => $this->slugs('mobile-application-development'),
                'description' => $this->trans(
                    'Native and cross-platform mobile applications for iOS and Android. We leverage Flutter, React Native, and native technologies to deliver pixel-perfect, high-performance mobile experiences that users love.',
                    'تطبيقات هواتف أصلية ومتعددة المنصات لنظامي iOS و Android. نستفيد من Flutter و React Native والتقنيات الأصلية لتقديم تجارب هواتف عالية الأداء ومثالية البكسل يحبها المستخدمون.',
                ),
                'deliverables' => $this->trans(
                    "iOS and Android native app development\nCross-platform development with Flutter\nUI/UX optimization for mobile\nPush notifications and real-time features\nApp Store and Google Play deployment",
                    "تطوير تطبيقات iOS و Android الأصلية\nتطوير متعدد المنصات باستخدام Flutter\nتحسين واجهة المستخدم للهواتف\nالإشعارات الفورية والميزات في الوقت الحقيقي\nالنشر على App Store و Google Play",
                ),
                'icon' => 'device-mobile',
            ],
            [
                'title' => $this->trans('UI/UX Design & Strategy', 'تصميم واستراتيجية واجهة المستخدم', 'Design UI/UX et strategie', 'Diseno UI/UX y estrategia'),
                'slug' => $this->slugs('ui-ux-design-strategy'),
                'description' => $this->trans(
                    'User-centered design that combines aesthetics with functionality. Our design team creates intuitive interfaces through extensive user research, wireframing, prototyping, and usability testing to ensure exceptional digital experiences.',
                    'تصميم يركز على المستخدم يجمع بين الجمال والوظائف. يصمم فريقنا واجهات بديهية من خلال بحث مستخدم مكثف وإطارات سلكية ونماذج أولية واختبار قابلية الاستخدام لضمان تجارب رقمية استثنائية.',
                ),
                'deliverables' => $this->trans(
                    "User research and persona development\nWireframes and interactive prototypes\nVisual design and design systems\nUsability testing and iteration\nDesign handoff with developer specifications",
                    "بحث المستخدم وتطوير الشخصيات\nإطارات سلكية ونماذج أولية تفاعلية\nتصميم بصري وأنظمة تصميم\naختبار قابلية الاستخدام والتكرار\nتسليم التصميم مع مواصفات المطورين",
                ),
                'icon' => 'palette',
            ],
            [
                'title' => $this->trans('Enterprise Software Solutions', 'حلول البرمجيات المؤسسية', 'Solutions logicielles d\'entreprise', 'Soluciones de software empresarial'),
                'slug' => $this->slugs('enterprise-software-solutions'),
                'description' => $this->trans(
                    'Tailored ERP, CRM, and enterprise-grade solutions designed to streamline operations, enhance productivity, and integrate seamlessly with existing business processes. Built for scale, security, and reliability.',
                    'حلول ERP و CRM مخصصة وحلول مؤسسية مصممة لتبسيط العمليات وتعزيز الإنتاجية والتكامل بسلاسة مع العمليات التجارية الحالية. مبنية للتوسع والأمان والموثوقية.',
                ),
                'deliverables' => $this->trans(
                    "Custom ERP and CRM development\nBusiness process automation\nLegacy system modernization\nEnterprise integration and middleware\nRole-based access control and security",
                    "تطوير ERP و CRM مخصص\nأتمتة العمليات التجارية\nتحديث الأنظمة القديمة\nتكامل المؤسسات والبرمجيات الوسيطة\nالتحكم في الوصول حسب الأدوار والأمان",
                ),
                'icon' => 'building-office',
            ],
            [
                'title' => $this->trans('Cloud Infrastructure & DevOps', 'البنية التحتية السحابية و DevOps', 'Infrastructure cloud et DevOps', 'Infraestructura en la nube y DevOps'),
                'slug' => $this->slugs('cloud-infrastructure-devops'),
                'description' => $this->trans(
                    'End-to-end cloud infrastructure management, CI/CD pipelines, containerization, and DevOps consulting. We help you deploy faster, scale efficiently, and maintain robust production environments on AWS, GCP, and Azure.',
                    'إدارة شاملة للبنية التحتية السحابية وخطوط CI/CD والحاويات واستشارات DevOps. نساعدك على النشر بشكل أسرع والتوسع بكفاءة والحفاظ على بيئات إنتاج قوية على AWS و GCP و Azure.',
                ),
                'deliverables' => $this->trans(
                    "Cloud architecture design and migration\nCI/CD pipeline setup and automation\nDocker and Kubernetes orchestration\nInfrastructure monitoring and alerting\nCost optimization and scaling strategies",
                    "تصميم البنية السحابية والهجرة\nإعداد خطوط CI/CD والأتمتة\nإدارة Docker و Kubernetes\nمراقبة البنية التحتية والتنبيه\nتحسين التكاليف واستراتيجيات التوسع",
                ),
                'icon' => 'cloud',
            ],
            [
                'title' => $this->trans('Technology Consulting', 'الاستشارات التقنية', 'Conseil technologique', 'Consultoria tecnologica'),
                'slug' => $this->slugs('technology-consulting'),
                'description' => $this->trans(
                    'Expert technology advisory services covering architecture reviews, tech stack selection, digital transformation roadmaps, and technical due diligence. We help you make informed decisions that align technology with your business goals.',
                    'خدمات استشارية تقنية متخصصة تشمل مراجعات البنية واختيار التقنيات وخرائط طريق التحول الرقمي والعناية التقنية الواجبة. نساعدك على اتخاذ قرارات مدروسة تربط التكنولوجيا بأهداف عملك.',
                ),
                'deliverables' => $this->trans(
                    "Technology assessment and audit\nArchitecture review and recommendations\nDigital transformation roadmap\nTech stack evaluation and selection\nTechnical due diligence for investments",
                    "تقييم التكنولوجيا والتدقيق\nمراجعة البنية والتوصيات\nخارطة طريق التحول الرقمي\nتقييم واختيار التقنيات\nالعناية التقنية الواجبة للاستثمارات",
                ),
                'icon' => 'light-bulb',
            ],
        ];

        foreach ($serviceData as $i => $s) {
            $service = Service::create([
                'title' => $s['title'],
                'slug' => $s['slug'],
                'description' => $s['description'],
                'deliverables' => $s['deliverables'] ?? null,
                'icon' => $s['icon'],
                'sort_order' => $i,
                'is_active' => true,
            ]);
            $services[] = $service;

            // MediaFile for service
            MediaFile::create([
                'type' => 'image',
                'path' => "https://picsum.photos/seed/service-{$i}/800/600",
                'storage' => 'url',
                'alt' => $this->trans($s['title']['en'] ?? 'Service image'),
                'mediable_type' => Service::class,
                'mediable_id' => $service->id,
                'collection' => 'featured',
                'sort_order' => 0,
            ]);
        }

        // =====================================================================
        // 4. Industries
        // =====================================================================
        $industries = [];
        $industryData = [
            [
                'title' => $this->trans('Healthcare & Medical', 'الرعاية الصحية والطبية', 'Sante et medical', 'Salud y medicina'),
                'slug' => $this->slugs('healthcare-medical'),
                'description' => $this->trans(
                    'Digital solutions for healthcare providers, hospitals, and medical institutions. We build HIPAA-compliant platforms including electronic health records, telemedicine systems, appointment scheduling, and patient engagement tools.',
                    'حلول رقمية لمقدمي الرعاية الصحية والمستشفيات والمؤسسات الطبية. نبني منصات متوافقة مع معايير HIPAA تشمل السجلات الصحية الإلكترونية وأنظمة الطب عن بعد وجدولة المواعيد وأدوات مشاركة المرضى.',
                ),
                'challenges' => $this->trans(
                    "Data privacy and regulatory compliance\nInteroperability between legacy systems\nReal-time patient monitoring requirements\nSecure data exchange between providers",
                    "خصوصية البيانات والامتثال التنظيمي\nالتوافق بين الأنظمة القديمة\nمتطلبات مراقبة المرضى في الوقت الفعلي\nتبادل البيانات الآمن بين مقدمي الخدمات",
                ),
                'solutions' => $this->trans(
                    "End-to-end encrypted health platforms\nHL7/FHIR integration capabilities\nReal-time dashboards and analytics\nTelemedicine and remote care solutions",
                    "منصات صحية مشفرة من البداية للنهاية\nقدرات تكامل HL7/FHIR\nلوحات معلومات وتحليلات في الوقت الفعلي\nحلول الطب عن بعد والرعاية عن بُعد",
                ),
                'icon' => 'heart',
            ],
            [
                'title' => $this->trans('E-Commerce & Retail', 'التجارة الإلكترونية والتجزئة', 'Commerce electronique et vente au detail', 'Comercio electronico y retail'),
                'slug' => $this->slugs('e-commerce-retail'),
                'description' => $this->trans(
                    'Comprehensive e-commerce solutions from custom storefronts to marketplace platforms. We integrate inventory management, payment processing, logistics, and analytics to create seamless shopping experiences that convert.',
                    'حلول تجارة إلكترونية شاملة من واجهات المتاجر المخصصة إلى منصات الأسواق. ندمج إدارة المخزون ومعالجة المدفوعات واللوجستيات والتحليلات لإنشاء تجارب تسوق سلسة تحقق التحويلات.',
                ),
                'challenges' => $this->trans(
                    "High traffic during peak seasons\nComplex inventory management\nMulti-currency and multi-language support\nSecure payment processing",
                    "حركة مرور عالية خلال المواسم\nإدارة مخزون معقدة\nدعم العملات واللغات المتعددة\nمعالجة مدفوعات آمنة",
                ),
                'solutions' => $this->trans(
                    "Auto-scaling cloud architecture\nReal-time inventory synchronization\nMulti-tenant marketplace platforms\nPCI-DSS compliant payment integration",
                    "بنية سحابية قابلة للتوسع التلقائي\nمزامنة المخزون في الوقت الفعلي\nمنصات أسواق متعددة المستأجرين\nتكامل مدفوعات متوافق مع PCI-DSS",
                ),
                'icon' => 'shopping-cart',
            ],
            [
                'title' => $this->trans('Financial Services', 'الخدمات المالية', 'Services financiers', 'Servicios financieros'),
                'slug' => $this->slugs('financial-services'),
                'description' => $this->trans(
                    'Fintech solutions, banking applications, and financial management systems built with the highest security standards. We develop digital banking platforms, payment gateways, risk management tools, and regulatory compliance systems.',
                    'حلول التكنولوجيا المالية وتطبيقات البنوك وأنظمة الإدارة المالية المبنية بأعلى معايير الأمان. نطور منصات البنوك الرقمية وبوابات الدفع وأدوات إدارة المخاطر وأنظمة الامتثال التنظيمي.',
                ),
                'challenges' => $this->trans(
                    "Regulatory compliance across jurisdictions\nFraud detection and prevention\nReal-time transaction processing\nData encryption and security",
                    "الامتثال التنظيمي عبر الولايات القضائية\nكشف ومنع الاحتيال\nمعالجة المعاملات في الوقت الفعلي\nتشفير البيانات والأمان",
                ),
                'solutions' => $this->trans(
                    "Multi-layer security architecture\nAI-powered fraud detection\nReal-time payment processing engines\nAutomated regulatory reporting",
                    "بنية أمان متعددة الطبقات\nكشف الاحتيال بالذكاء الاصطناعي\nمحركات معالجة المدفوعات الفورية\nتقارير تنظيمية آلية",
                ),
                'icon' => 'banknotes',
            ],
            [
                'title' => $this->trans('Education & EdTech', 'التعليم وتكنولوجيا التعليم', 'Education et EdTech', 'Educacion y EdTech'),
                'slug' => $this->slugs('education-edtech'),
                'description' => $this->trans(
                    'Learning management systems, virtual classrooms, and educational technology platforms. We create engaging e-learning experiences with interactive content delivery, progress tracking, assessments, and certification management.',
                    'أنظمة إدارة التعلم والفصول الدراسية الافتراضية ومنصات تكنولوجيا التعليم. ننشئ تجارب تعلم إلكتروني جذابة مع توصيل محتوى تفاعلي وتتبع التقدم والتقييمات وإدارة الشهادات.',
                ),
                'challenges' => $this->trans(
                    "Engaging diverse learner populations\nScaling for thousands of concurrent users\nAccessibility and inclusivity requirements\nContent management and versioning",
                    "إشراك مجموعات متنوعة من المتعلمين\nالتوسع لآلاف المستخدمين المتزامنين\nمتطلبات إمكانية الوصول والشمولية\nإدارة المحتوى والإصدارات",
                ),
                'solutions' => $this->trans(
                    "Adaptive learning algorithms\nReal-time video and collaboration tools\nWCAG 2.1 AA compliant platforms\nHeadless CMS for flexible content delivery",
                    "خوارزميات التعلم التكيفي\nأدوات الفيديو والتعاون في الوقت الفعلي\nمنصات متوافقة مع WCAG 2.1 AA\nCMS بدون واجهة لتوصيل محتوى مرن",
                ),
                'icon' => 'academic-cap',
            ],
        ];

        foreach ($industryData as $i => $ind) {
            $industry = Industry::create([
                'title' => $ind['title'],
                'slug' => $ind['slug'],
                'description' => $ind['description'],
                'challenges' => $ind['challenges'] ?? null,
                'solutions' => $ind['solutions'] ?? null,
                'icon' => $ind['icon'],
                'sort_order' => $i,
                'is_active' => true,
            ]);
            $industries[] = $industry;
        }

        // =====================================================================
        // 5. Tech Categories & Items
        // =====================================================================
        $techItems = [];
        $techData = [
            [
                'name' => $this->trans('Backend', 'الخلفية'),
                'slug' => 'backend',
                'items' => [
                    ['name' => $this->trans('Laravel', 'لارافيل'), 'url' => 'https://laravel.com'],
                    ['name' => $this->trans('Node.js', 'نود جي إس'), 'url' => 'https://nodejs.org'],
                    ['name' => $this->trans('Python', 'بايثون'), 'url' => 'https://python.org'],
                    ['name' => $this->trans('Go', 'جو'), 'url' => 'https://go.dev'],
                ],
            ],
            [
                'name' => $this->trans('Frontend', 'الواجهة الأمامية'),
                'slug' => 'frontend',
                'items' => [
                    ['name' => $this->trans('Vue.js', 'فيو جي إس'), 'url' => 'https://vuejs.org'],
                    ['name' => $this->trans('React', 'رياكت'), 'url' => 'https://react.dev'],
                    ['name' => $this->trans('TypeScript', 'تايب سكريبت'), 'url' => 'https://typescriptlang.org'],
                    ['name' => $this->trans('Tailwind CSS', 'تيلويند سي إس إس'), 'url' => 'https://tailwindcss.com'],
                ],
            ],
            [
                'name' => $this->trans('Mobile', 'الهواتف الذكية'),
                'slug' => 'mobile',
                'items' => [
                    ['name' => $this->trans('Flutter', 'فلاتر'), 'url' => 'https://flutter.dev'],
                    ['name' => $this->trans('React Native', 'رياكت نيتف'), 'url' => 'https://reactnative.dev'],
                    ['name' => $this->trans('Swift', 'سويفت'), 'url' => 'https://swift.org'],
                    ['name' => $this->trans('Kotlin', 'كوتلن'), 'url' => 'https://kotlinlang.org'],
                ],
            ],
            [
                'name' => $this->trans('Database', 'قواعد البيانات'),
                'slug' => 'database',
                'items' => [
                    ['name' => $this->trans('PostgreSQL', 'بوستجرس كيو إل'), 'url' => 'https://postgresql.org'],
                    ['name' => $this->trans('MySQL', 'ماي إس كيو إل'), 'url' => 'https://mysql.com'],
                    ['name' => $this->trans('MongoDB', 'مونجو دي بي'), 'url' => 'https://mongodb.com'],
                    ['name' => $this->trans('Redis', 'ريدس'), 'url' => 'https://redis.io'],
                ],
            ],
            [
                'name' => $this->trans('Cloud', 'السحابة'),
                'slug' => 'cloud',
                'items' => [
                    ['name' => $this->trans('AWS', 'أمازون ويب سيرفسز'), 'url' => 'https://aws.amazon.com'],
                    ['name' => $this->trans('Google Cloud', 'جوجل كلاود'), 'url' => 'https://cloud.google.com'],
                    ['name' => $this->trans('Azure', 'أزور'), 'url' => 'https://azure.microsoft.com'],
                    ['name' => $this->trans('DigitalOcean', 'ديجيتال أوشن'), 'url' => 'https://digitalocean.com'],
                ],
            ],
            [
                'name' => $this->trans('DevOps', 'ديف أوبس'),
                'slug' => 'devops',
                'items' => [
                    ['name' => $this->trans('Docker', 'دوكر'), 'url' => 'https://docker.com'],
                    ['name' => $this->trans('Kubernetes', 'كوبرنيتيس'), 'url' => 'https://kubernetes.io'],
                    ['name' => $this->trans('GitHub Actions', 'جيت هب أكشنز'), 'url' => 'https://github.com/features/actions'],
                    ['name' => $this->trans('Terraform', 'تيرافورم'), 'url' => 'https://terraform.io'],
                ],
            ],
        ];

        foreach ($techData as $catIndex => $catData) {
            $cat = TechCategory::create([
                'name' => $catData['name'],
                'slug' => $catData['slug'],
                'sort_order' => $catIndex,
            ]);

            foreach ($catData['items'] as $j => $itemData) {
                $techItems[] = TechItem::create([
                    'name' => $itemData['name'],
                    'url' => $itemData['url'],
                    'tech_category_id' => $cat->id,
                    'sort_order' => $j,
                ]);
            }
        }

        // =====================================================================
        // 6. Case Studies
        // =====================================================================
        $caseStudyData = [
            [
                'title' => $this->trans('MedConnect Pro', 'ميد كونكت برو'),
                'slug' => $this->slugs('medconnect-pro'),
                'excerpt' => $this->trans(
                    'A comprehensive healthcare platform connecting patients with providers through telemedicine, electronic health records, and intelligent appointment scheduling.',
                    'منصة رعاية صحية شاملة تربط المرضى بمقدمي الخدمات من خلال الطب عن بعد والسجلات الصحية الإلكترونية والجدولة الذكية للمواعيد.',
                ),
                'overview' => $this->trans(
                    'MedConnect Pro is a full-featured healthcare platform designed for a network of 50+ clinics across Saudi Arabia. The platform integrates telemedicine capabilities, electronic health records (EHR), prescription management, lab result tracking, and an AI-powered symptom checker to deliver a seamless healthcare experience for both patients and medical professionals.',
                    'ميد كونكت برو هي منصة رعاية صحية متكاملة مصممة لشبكة تضم أكثر من 50 عيادة في المملكة العربية السعودية. تدمج المنصة قدرات الطب عن بعد والسجلات الصحية الإلكترونية وإدارة الوصفات الطبية وتتبع نتائج المختبر ومدقق أعراض مدعوم بالذكاء الاصطناعي لتقديم تجربة رعاية صحية سلسة للمرضى والمهنيين الطبيين.',
                ),
                'challenge' => $this->trans(
                    'The client operated 50+ clinics with disparate legacy systems that could not communicate with each other. Patient data was siloed, appointment scheduling was manual and error-prone, and there was no telemedicine capability. The system needed to comply with Saudi Arabia\'s NDMO data regulations while serving over 200,000 registered patients.',
                    'كان العميل يدير أكثر من 50 عيادة بأنظمة قديمة متباينة لا يمكنها التواصل مع بعضها البعض. كانت بيانات المرضى معزولة وجدولة المواعيد يدوية وعرضة للأخطاء ولم تكن هناك قدرة للطب عن بعد. احتاج النظام للامتثال لأنظمة بيانات NDMO السعودية مع خدمة أكثر من 200,000 مريض مسجل.',
                ),
                'solution' => $this->trans(
                    'We designed a microservices architecture with a unified patient portal, real-time video consultation module, and centralized EHR system. The platform features role-based access for doctors, nurses, pharmacists, and administrators, with end-to-end encryption for all patient data.',
                    'صممنا بنية خدمات مصغرة مع بوابة مريض موحدة ووحدة استشارات فيديو في الوقت الفعلي ونظام سجلات صحية إلكترونية مركزي. تتميز المنصة بالتحكم في الوصول حسب الأدوار للأطباء والممرضين والصيادلة والإداريين مع تشفير شامل لجميع بيانات المرضى.',
                ),
                'features' => $this->trans(
                    "HD video consultations with screen sharing\nAI-powered symptom checker and triage\nElectronic prescriptions with pharmacy integration\nReal-time lab results and medical imaging viewer\nMulti-language support (Arabic, English, Urdu)\nInsurance verification and claims processing",
                    "استشارات فيديو عالية الدقة مع مشاركة الشاشة\nمدقق أعراض وفرز مدعوم بالذكاء الاصطناعي\nوصفات إلكترونية مع تكامل الصيدليات\nنتائج مختبر في الوقت الفعلي وعارض صور طبية\nدعم متعدد اللغات (العربية والإنجليزية والأردية)\nالتحقق من التأمين ومعالجة المطالبات",
                ),
                'our_role' => $this->trans(
                    'Full project lifecycle from requirements gathering through deployment and ongoing maintenance. Our team of 12 engineers worked in 2-week sprints with continuous delivery.',
                    'دورة المشروع الكاملة من جمع المتطلبات إلى النشر والصيانة المستمرة. عمل فريقنا المكون من 12 مهندساً في سباقات أسبوعين مع تسليم مستمر.',
                ),
                'results' => $this->trans(
                    "40% reduction in patient wait times\n95% patient satisfaction rating\n200,000+ registered patients\n50,000+ telemedicine consultations in first year\n99.9% system uptime",
                    "تخفيض 40% في أوقات انتظار المرضى\nتقييم رضا المرضى 95%\nأكثر من 200,000 مريض مسجل\nأكثر من 50,000 استشارة طب عن بعد في السنة الأولى\nوقت تشغيل النظام 99.9%",
                ),
                'timeline' => '8 months',
                'team_size' => '12 engineers',
                'year' => 2025,
                'is_featured' => true,
                'service_index' => 0, // Web App Dev
                'industry_index' => 0, // Healthcare
                'tech_indices' => [0, 4, 8, 12, 16, 20], // Laravel, Vue, Flutter, PostgreSQL, AWS, Docker
            ],
            [
                'title' => $this->trans('ShopSphere', 'شوب سفير'),
                'slug' => $this->slugs('shopsphere'),
                'excerpt' => $this->trans(
                    'A multi-vendor e-commerce marketplace handling 10,000+ products with real-time inventory, multi-currency support, and AI-powered product recommendations.',
                    'سوق تجارة إلكترونية متعدد البائعين يتعامل مع أكثر من 10,000 منتج مع جرد فوري ودعم عملات متعددة وتوصيات منتجات مدعومة بالذكاء الاصطناعي.',
                ),
                'overview' => $this->trans(
                    'ShopSphere is a full-featured multi-vendor marketplace platform built for a leading Saudi retail group. The platform supports 500+ vendors, 10,000+ SKUs, multiple payment gateways, real-time inventory synchronization, and an AI recommendation engine that increased average order value by 35%.',
                    'شوب سفير هي منصة سوق متعددة البائعين كاملة المزايا مبنية لمجموعة تجزئة سعودية رائدة. تدعم المنصة أكثر من 500 بائع وأكثر من 10,000 وحدة SKU وبوابات دفع متعددة ومزامنة المخزون في الوقت الفعلي ومحرك توصيات ذكاء اصطناعي زاد متوسط قيمة الطلب بنسبة 35%.',
                ),
                'challenge' => $this->trans(
                    'The client needed a platform that could handle 50,000+ concurrent users during flash sales and seasonal promotions, support vendors with varying product catalogs, and process payments in multiple currencies (SAR, AED, USD, EUR) while maintaining sub-second page load times.',
                    'احتاج العميل إلى منصة يمكنها التعامل مع أكثر من 50,000 مستخدم متزامن خلال العروض السريعة والعروض الموسمية ودعم البائعين بكتالوجات منتجات متنوعة ومعالجة المدفوعات بعملات متعددة (ريال سعودي ودرهم إماراتي ودولار أمريكي ويورو) مع الحفاظ على أوقات تحميل صفحة أقل من ثانية.',
                ),
                'solution' => $this->trans(
                    'We built an event-driven microservices architecture with Redis caching, Elasticsearch for product search, and auto-scaling Kubernetes clusters. The frontend uses server-side rendering for optimal SEO and instant page loads.',
                    'بنينا بنية خدمات مصغرة مبنية على الأحداث مع تخزين مؤقت Redis و Elasticsearch للبحث عن المنتجات ومجموعات Kubernetes قابلة للتوسع التلقائي. تستخدم الواجهة الأمامية التصيير من جانب الخادم لتحسين SEO والتحميل الفوري للصفحات.',
                ),
                'features' => $this->trans(
                    "Multi-vendor dashboard with analytics\nAI-powered product recommendations\nReal-time inventory across warehouses\nMulti-currency payment processing\nAdvanced search with filters and facets\nAutomated vendor settlement and reporting",
                    "لوحة معلومات متعددة البائعين مع التحليلات\nتوصيات منتجات مدعومة بالذكاء الاصطناعي\nجرد فوري عبر المستودعات\nمعالجة مدفوعات متعددة العملات\nبحث متقدم مع فلاتر ومحاور\nتسوية البائعين والتقارير الآلية",
                ),
                'our_role' => $this->trans(
                    'End-to-end platform development including backend architecture, frontend implementation, DevOps setup, and performance optimization. We also provided vendor onboarding support.',
                    'تطوير المنصة الشامل بما في ذلك البنية الخلفية وتنفيذ الواجهة الأمامية وإعداد DevOps وتحسين الأداء. قدمنا أيضاً دعم إعداد البائعين.',
                ),
                'results' => $this->trans(
                    "500+ active vendors onboarded\n35% increase in average order value\n99.99% uptime during peak sales\n2.5M+ monthly active users\nSub-200ms average API response time",
                    "أكثر من 500 بائع نشط\nزيادة 35% في متوسط قيمة الطلب\nوقت تشغيل 99.99% خلال ذروة المبيعات\nأكثر من 2.5 مليون مستخدم نشط شهرياً\nمتوسط وقت استجابة API أقل من 200 مللي ثانية",
                ),
                'timeline' => '10 months',
                'team_size' => '15 engineers',
                'year' => 2025,
                'is_featured' => true,
                'service_index' => 0, // Web App Dev
                'industry_index' => 1, // E-Commerce
                'tech_indices' => [1, 5, 13, 15, 16, 21], // Node.js, React, MongoDB, Redis, AWS, Kubernetes
            ],
            [
                'title' => $this->trans('EduVerse', 'إيدو فيرس'),
                'slug' => $this->slugs('eduverse'),
                'excerpt' => $this->trans(
                    'An adaptive learning platform serving 100,000+ students with personalized learning paths, live virtual classrooms, and comprehensive assessment tools.',
                    'منصة تعلم تكيفية تخدم أكثر من 100,000 طالب بمسارات تعلم مخصصة وفصول دراسية افتراضية حية وأدوات تقييم شاملة.',
                ),
                'overview' => $this->trans(
                    'EduVerse is an enterprise learning management system developed for a major Saudi educational institution. It serves 100,000+ students and 5,000+ instructors with adaptive learning paths, live video classrooms, automated grading, and detailed analytics dashboards for administrators and parents.',
                    'إيدو فيرس هو نظام إدارة تعلم مؤسسي تم تطويره لمؤسسة تعليمية سعودية كبرى. يخدم أكثر من 100,000 طالب وأكثر من 5,000 مدرس بمسارات تعلم تكيفية وفصول فيديو حية وتصحيح آلي ولوحات تحليلات تفصيلية للإداريين وأولياء الأمور.',
                ),
                'challenge' => $this->trans(
                    'The institution needed to modernize from paper-based processes to a fully digital learning environment that could support 100,000+ students simultaneously, accommodate Arabic and English content, and provide real-time progress tracking for students, teachers, and parents.',
                    'احتاجت المؤسسة إلى التحديث من العمليات الورقية إلى بيئة تعلم رقمية كاملة يمكنها دعم أكثر من 100,000 طالب في وقت واحد واستيعاب المحتوى باللغتين العربية والإنجليزية وتوفير تتبع التقدم في الوقت الفعلي للطلاب والمعلمين وأولياء الأمور.',
                ),
                'solution' => $this->trans(
                    'We developed a cloud-native platform with adaptive learning algorithms, WebRTC-based live classrooms, a content management system supporting rich multimedia content, and a mobile app for on-the-go learning. The platform automatically adjusts difficulty based on student performance.',
                    'طورنا منصة سحابية أصلية مع خوارزميات تعلم تكيفية وفصول حية قائمة على WebRTC ونظام إدارة محتوى يدعم محتوى وسائط متعددة غنية وتطبيق هاتف للتعلم أثناء التنقل. تضبط المنصة تلقائياً الصعوبة بناءً على أداء الطالب.',
                ),
                'features' => $this->trans(
                    "Adaptive learning paths with AI\nLive HD video classrooms with breakout rooms\nAutomated grading and plagiarism detection\nParent portal with progress notifications\nOffline content access via mobile app\nComprehensive analytics and reporting",
                    "مسارات تعلم تكيفية بالذكاء الاصطناعي\nفصول فيديو حية عالية الدقة مع غرف فرعية\nتصحيح آلي وكشف الانتحال\nبوابة أولياء الأمور مع إشعارات التقدم\nوصول للمحتوى بدون إنترنت عبر تطبيق الهاتف\nتحليلات وتقارير شاملة",
                ),
                'our_role' => $this->trans(
                    'Complete platform design and development, including mobile apps for iOS and Android, admin dashboards, content management tools, and integration with the institution\'s existing student information system.',
                    'تصميم وتطوير المنصة الكامل بما في ذلك تطبيقات الهاتف لنظامي iOS و Android ولوحات الإدارة وأدوات إدارة المحتوى والتكامل مع نظام معلومات الطلاب الحالي للمؤسسة.',
                ),
                'results' => $this->trans(
                    "100,000+ active students\n92% student engagement rate\n30% improvement in test scores\n5,000+ instructors onboarded\n4.8/5 average app store rating",
                    "أكثر من 100,000 طالب نشط\nمعدل مشاركة الطلاب 92%\nتحسن 30% في درجات الاختبارات\nأكثر من 5,000 مدرس\nتقييم متوسط 4.8/5 في متجر التطبيقات",
                ),
                'timeline' => '12 months',
                'team_size' => '18 engineers',
                'year' => 2024,
                'is_featured' => true,
                'service_index' => 1, // Mobile App Dev
                'industry_index' => 3, // Education
                'tech_indices' => [0, 4, 8, 12, 17, 20], // Laravel, Vue, Flutter, PostgreSQL, GCP, Docker
            ],
        ];

        foreach ($caseStudyData as $i => $data) {
            $cs = CaseStudy::create([
                'title' => $data['title'],
                'slug' => $data['slug'],
                'excerpt' => $data['excerpt'],
                'overview' => $data['overview'],
                'challenge' => $data['challenge'],
                'solution' => $data['solution'],
                'features' => $data['features'] ?? null,
                'our_role' => $data['our_role'] ?? null,
                'results' => $data['results'] ?? null,
                'timeline' => $data['timeline'] ?? null,
                'team_size' => $data['team_size'] ?? null,
                'year' => $data['year'],
                'is_featured' => $data['is_featured'],
                'sort_order' => $i,
                'is_active' => true,
            ]);

            // Attach relations
            $cs->services()->attach($services[$data['service_index']]->id);
            $cs->industries()->attach($industries[$data['industry_index']]->id);

            $techIds = array_map(fn($idx) => $techItems[$idx]->id, $data['tech_indices']);
            $cs->techItems()->attach($techIds);

            // MediaFile for case study
            MediaFile::create([
                'type' => 'image',
                'path' => "https://picsum.photos/seed/case-study-{$i}/800/600",
                'storage' => 'url',
                'alt' => $this->trans($data['title']['en'] ?? 'Case study image'),
                'mediable_type' => CaseStudy::class,
                'mediable_id' => $cs->id,
                'collection' => 'thumbnail',
                'sort_order' => 0,
            ]);

            // Additional gallery images
            for ($g = 1; $g <= 3; $g++) {
                MediaFile::create([
                    'type' => 'image',
                    'path' => "https://picsum.photos/seed/case-study-{$i}-gallery-{$g}/800/600",
                    'storage' => 'url',
                    'alt' => $this->trans("Screenshot {$g}"),
                    'mediable_type' => CaseStudy::class,
                    'mediable_id' => $cs->id,
                    'collection' => 'gallery',
                    'sort_order' => $g,
                ]);
            }
        }

        // =====================================================================
        // 7. Testimonials
        // =====================================================================
        $testimonialData = [
            [
                'name' => $this->trans('Dr. Khalid Al-Mansour', 'د. خالد المنصور'),
                'position' => $this->trans('Chief Technology Officer', 'المدير التقني'),
                'company' => $this->trans('National Health Group', 'المجموعة الصحية الوطنية'),
                'content' => $this->trans(
                    'Around Solutions transformed our entire healthcare infrastructure. Their team delivered MedConnect Pro ahead of schedule, and the platform has fundamentally changed how we serve our patients. The attention to security and compliance was exceptional.',
                    'حوّلت حلول أراوند بنيتنا التحتية للرعاية الصحية بالكامل. سلّم فريقهم ميد كونكت برو قبل الموعد المحدد وقد غيرت المنصة بشكل جذري طريقة خدمتنا لمرضانا. كان الاهتمام بالأمان والامتثال استثنائياً.',
                ),
                'rating' => 5,
                'avatar' => 'https://ui-avatars.com/api/?name=Khalid+AlMansour&background=dc2626&color=fff&size=400',
            ],
            [
                'name' => $this->trans('Sarah Al-Rashidi', 'سارة الراشدي'),
                'position' => $this->trans('VP of Digital Commerce', 'نائبة رئيس التجارة الرقمية'),
                'company' => $this->trans('Gulf Retail Group', 'مجموعة الخليج للتجزئة'),
                'content' => $this->trans(
                    'The ShopSphere marketplace platform exceeded every expectation. During our first Ramadan sale, we handled 50,000 concurrent users with zero downtime. The AI recommendation engine alone increased our average order value by 35%. Around Solutions is now our go-to technology partner.',
                    'تجاوزت منصة سوق شوب سفير كل التوقعات. خلال أول بيع لنا في رمضان تعاملنا مع 50,000 مستخدم متزامن بدون أي توقف. محرك التوصيات بالذكاء الاصطناعي وحده زاد متوسط قيمة الطلب بنسبة 35%. حلول أراوند هي الآن شريكنا التقني المفضل.',
                ),
                'rating' => 5,
                'avatar' => 'https://ui-avatars.com/api/?name=Sarah+AlRashidi&background=dc2626&color=fff&size=400',
            ],
            [
                'name' => $this->trans('Prof. Abdullah Al-Otaibi', 'أ.د. عبدالله العتيبي'),
                'position' => $this->trans('Dean of Digital Learning', 'عميد التعلم الرقمي'),
                'company' => $this->trans('Riyadh Education Foundation', 'مؤسسة الرياض التعليمية'),
                'content' => $this->trans(
                    'EduVerse has revolutionized how we deliver education. The adaptive learning paths have improved student outcomes by 30%, and the platform seamlessly handles our 100,000+ student body. The Around Solutions team truly understood the unique challenges of Arabic-first educational technology.',
                    'أحدث إيدو فيرس ثورة في طريقة تقديمنا للتعليم. حسّنت مسارات التعلم التكيفية نتائج الطلاب بنسبة 30% وتتعامل المنصة بسلاسة مع أكثر من 100,000 طالب. فريق حلول أراوند فهم حقاً التحديات الفريدة لتكنولوجيا التعليم العربية أولاً.',
                ),
                'rating' => 5,
                'avatar' => 'https://ui-avatars.com/api/?name=Abdullah+AlOtaibi&background=dc2626&color=fff&size=400',
            ],
            [
                'name' => $this->trans('Mohammed Al-Harbi', 'محمد الحربي'),
                'position' => $this->trans('CIO', 'مدير تقنية المعلومات'),
                'company' => $this->trans('Peninsular Financial', 'بنينسولار المالية'),
                'content' => $this->trans(
                    'We engaged Around Solutions for a complete technology audit and digital transformation roadmap. Their consulting team provided actionable insights that saved us millions in potential wrong technology investments. Their expertise in enterprise architecture is second to none.',
                    'تعاقدنا مع حلول أراوند لإجراء تدقيق تقني شامل وخارطة طريق للتحول الرقمي. قدم فريقهم الاستشاري رؤى عملية وفرت لنا ملايين في استثمارات تقنية خاطئة محتملة. خبرتهم في البنية المؤسسية لا مثيل لها.',
                ),
                'rating' => 4,
                'avatar' => 'https://ui-avatars.com/api/?name=Mohammed+AlHarbi&background=dc2626&color=fff&size=400',
            ],
        ];

        foreach ($testimonialData as $i => $t) {
            Testimonial::create([
                'name' => $t['name'],
                'position' => $t['position'],
                'company' => $t['company'],
                'content' => $t['content'],
                'avatar' => $t['avatar'],
                'rating' => $t['rating'],
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }

        // =====================================================================
        // 8. Clients
        // =====================================================================
        $clientData = [
            ['name' => $this->trans('Saudi Aramco', 'أرامكو السعودية'), 'url' => 'https://aramco.com', 'logo' => 'https://logo.clearbit.com/aramco.com'],
            ['name' => $this->trans('STC', 'الاتصالات السعودية'), 'url' => 'https://stc.com.sa', 'logo' => 'https://logo.clearbit.com/stc.com.sa'],
            ['name' => $this->trans('NEOM', 'نيوم'), 'url' => 'https://neom.com', 'logo' => 'https://logo.clearbit.com/neom.com'],
            ['name' => $this->trans('SABIC', 'سابك'), 'url' => 'https://sabic.com', 'logo' => 'https://logo.clearbit.com/sabic.com'],
            ['name' => $this->trans('Saudi Airlines', 'الخطوط السعودية'), 'url' => 'https://saudia.com', 'logo' => 'https://logo.clearbit.com/saudia.com'],
            ['name' => $this->trans('Elm', 'علم'), 'url' => 'https://elm.sa', 'logo' => 'https://logo.clearbit.com/elm.sa'],
        ];

        foreach ($clientData as $i => $c) {
            Client::create([
                'name' => $c['name'],
                'url' => $c['url'],
                'logo' => $c['logo'],
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }

        // =====================================================================
        // 9. Team Members
        // =====================================================================
        $teamData = [
            [
                'name' => $this->trans('Ahmed Al-Hassan', 'أحمد الحسن'),
                'position' => $this->trans('CEO & Founder', 'الرئيس التنفيذي والمؤسس'),
                'bio' => $this->trans(
                    'Ahmed founded Around Solutions in 2018 with a vision to build world-class software from Saudi Arabia. With 15+ years of experience in technology leadership, he has led digital transformation projects for Fortune 500 companies across the Middle East.',
                    'أسس أحمد حلول أراوند في 2018 برؤية لبناء برمجيات عالمية المستوى من المملكة العربية السعودية. مع أكثر من 15 عاماً من الخبرة في القيادة التقنية قاد مشاريع التحول الرقمي لشركات Fortune 500 في الشرق الأوسط.',
                ),
                'avatar' => 'https://ui-avatars.com/api/?name=Ahmed+AlHassan&background=dc2626&color=fff&size=400',
            ],
            [
                'name' => $this->trans('Fatima Al-Rashid', 'فاطمة الراشد'),
                'position' => $this->trans('Chief Technology Officer', 'المديرة التقنية'),
                'bio' => $this->trans(
                    'Fatima brings 12+ years of engineering excellence to Around Solutions. Previously a senior architect at a leading global tech company, she oversees all technical decisions, architecture reviews, and engineering best practices across the organization.',
                    'تجلب فاطمة أكثر من 12 عاماً من التميز الهندسي إلى حلول أراوند. كانت سابقاً مهندسة معمارية أولى في شركة تقنية عالمية رائدة وتشرف على جميع القرارات التقنية ومراجعات البنية وأفضل الممارسات الهندسية في المنظمة.',
                ),
                'avatar' => 'https://ui-avatars.com/api/?name=Fatima+AlRashid&background=dc2626&color=fff&size=400',
            ],
            [
                'name' => $this->trans('Omar Khalil', 'عمر خليل'),
                'position' => $this->trans('Lead Full-Stack Developer', 'كبير مطوري Full-Stack'),
                'bio' => $this->trans(
                    'Omar is a passionate full-stack developer with deep expertise in Laravel, Vue.js, and cloud architecture. He leads our development team and is responsible for code quality, technical mentoring, and delivering complex projects on time.',
                    'عمر مطور full-stack شغوف بخبرة عميقة في Laravel و Vue.js وبنية السحابة. يقود فريق التطوير لدينا ومسؤول عن جودة الكود والإرشاد التقني وتسليم المشاريع المعقدة في الوقت المحدد.',
                ),
                'avatar' => 'https://ui-avatars.com/api/?name=Omar+Khalil&background=dc2626&color=fff&size=400',
            ],
            [
                'name' => $this->trans('Layla Ibrahim', 'ليلى إبراهيم'),
                'position' => $this->trans('Head of Design', 'رئيسة قسم التصميم'),
                'bio' => $this->trans(
                    'Layla leads our design team with a focus on creating inclusive, accessible digital experiences. With a background in cognitive psychology and human-computer interaction, she ensures every interface we build is both beautiful and intuitive.',
                    'تقود ليلى فريق التصميم لدينا مع التركيز على إنشاء تجارب رقمية شاملة وقابلة للوصول. بخلفية في علم النفس المعرفي والتفاعل بين الإنسان والحاسوب تضمن أن كل واجهة نبنيها جميلة وبديهية.',
                ),
                'avatar' => 'https://ui-avatars.com/api/?name=Layla+Ibrahim&background=dc2626&color=fff&size=400',
            ],
        ];

        foreach ($teamData as $i => $m) {
            $member = TeamMember::create([
                'name' => $m['name'],
                'position' => $m['position'],
                'bio' => $m['bio'] ?? null,
                'sort_order' => $i,
                'is_active' => true,
            ]);

            // MediaFile for team member avatar
            MediaFile::create([
                'type' => 'image',
                'path' => $m['avatar'],
                'storage' => 'url',
                'alt' => $this->trans($m['name']['en'] ?? 'Team member'),
                'mediable_type' => TeamMember::class,
                'mediable_id' => $member->id,
                'collection' => 'avatar',
                'sort_order' => 0,
            ]);
        }

        // =====================================================================
        // 10. Process Steps
        // =====================================================================
        $processData = [
            [
                'title' => $this->trans('Discovery', 'الاكتشاف', 'Decouverte', 'Descubrimiento'),
                'description' => $this->trans(
                    'We begin by deeply understanding your business, goals, target users, and technical requirements through workshops, interviews, and market research to define a clear project vision.',
                    'نبدأ بفهم عملك وأهدافك والمستخدمين المستهدفين والمتطلبات التقنية بعمق من خلال ورش العمل والمقابلات وأبحاث السوق لتحديد رؤية واضحة للمشروع.',
                ),
                'icon' => 'magnifying-glass',
            ],
            [
                'title' => $this->trans('Planning', 'التخطيط', 'Planification', 'Planificacion'),
                'description' => $this->trans(
                    'We create a detailed project roadmap with milestones, sprint plans, technical architecture decisions, and resource allocation to ensure transparent and predictable delivery.',
                    'نضع خارطة طريق مفصلة للمشروع بمراحل وخطط سباقات وقرارات بنية تقنية وتخصيص موارد لضمان تسليم شفاف وقابل للتنبؤ.',
                ),
                'icon' => 'clipboard-document-list',
            ],
            [
                'title' => $this->trans('Design', 'التصميم', 'Conception', 'Diseno'),
                'description' => $this->trans(
                    'Our design team creates wireframes, interactive prototypes, and polished visual designs. We iterate based on your feedback and validate with usability testing before development begins.',
                    'يصمم فريقنا إطارات سلكية ونماذج أولية تفاعلية وتصاميم بصرية مصقولة. نكرر بناءً على ملاحظاتك ونتحقق من خلال اختبار قابلية الاستخدام قبل بدء التطوير.',
                ),
                'icon' => 'paint-brush',
            ],
            [
                'title' => $this->trans('Development', 'التطوير', 'Developpement', 'Desarrollo'),
                'description' => $this->trans(
                    'Our engineers build the solution using agile methodology with 2-week sprints, daily standups, and regular demos. You receive progress updates and working builds throughout the process.',
                    'يبني مهندسونا الحل باستخدام منهجية رشيقة مع سباقات أسبوعين واجتماعات يومية وعروض منتظمة. تتلقى تحديثات التقدم وبنيات عمل طوال العملية.',
                ),
                'icon' => 'code-bracket',
            ],
            [
                'title' => $this->trans('Testing & QA', 'الاختبار وضمان الجودة', 'Tests et QA', 'Pruebas y QA'),
                'description' => $this->trans(
                    'Rigorous quality assurance including automated testing, manual testing, performance testing, security audits, and accessibility checks to ensure the highest quality standards.',
                    'ضمان جودة صارم يشمل الاختبار الآلي والاختبار اليدوي واختبار الأداء وتدقيقات الأمان وفحوصات إمكانية الوصول لضمان أعلى معايير الجودة.',
                ),
                'icon' => 'shield-check',
            ],
            [
                'title' => $this->trans('Launch & Support', 'الإطلاق والدعم', 'Lancement et support', 'Lanzamiento y soporte'),
                'description' => $this->trans(
                    'We handle deployment, monitoring setup, and production optimization. Post-launch, we provide ongoing support, maintenance, performance monitoring, and feature enhancements to keep your solution running at its best.',
                    'نتولى النشر وإعداد المراقبة وتحسين الإنتاج. بعد الإطلاق نقدم دعماً مستمراً وصيانة ومراقبة أداء وتحسينات للميزات للحفاظ على أفضل أداء لحلك.',
                ),
                'icon' => 'rocket-launch',
            ],
        ];

        foreach ($processData as $i => $p) {
            ProcessStep::create([
                'title' => $p['title'],
                'description' => $p['description'],
                'icon' => $p['icon'] ?? null,
                'sort_order' => $i,
            ]);
        }
    }
}

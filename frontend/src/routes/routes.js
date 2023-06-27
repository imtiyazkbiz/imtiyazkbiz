import DashboardLayout from "@/views/Layout/DashboardLayout.vue";
import AuthLayout from "@/views/Pages/AuthLayout.vue";
// GeneralViews
import NotFound from "@/views/GeneralViews/NotFoundPage.vue";

//  admin
const AllCompanies = () => import(/* webpackChunkName: "company" */ "@/views/Super/AllCompanies.vue");
const EditCompany = () => import(/* webpackChunkName: "company" */ "@/views/Super/EditCompany.vue");
const Certificates = () => import(/* webpackChunkName: "cert" */ "@/views/Super/Certificates.vue");
const Account = () => import(/* webpackChunkName: "dashboard" */ "@/views/Super/Account.vue");
const TutorialVideo = () => import(/* webpackChunkName: "tutorial" */ "@/views/Super/TutorialVideo.vue");
const TourPage = () => import(/* webpackChunkName: "tutorial" */ "@/views/Super/TourPage.vue");
const AddTutorialVideo = () => import(/* webpackChunkName: "tutorial" */ "@/views/Super/AddTutorialVideo.vue");
const AddTourVideo = () => import(/* webpackChunkName: "tutorial" */ "@/views/Super/AddTourVideo.vue");
const CreateCertificate = () => import(/* webpackChunkName: "cert" */ "@/views/Super/CreateCertificate.vue");
const Courses = () => import(/* webpackChunkName: "course" */ "@/views/Super/Courses.vue");
const CreateCourse = () => import(/* webpackChunkName: "course" */ "@/views/Super/CreateCourse.vue");
const CourseFolder = () => import(/* webpackChunkName: "course" */ "@/views/Super/CourseFolder.vue");
const CourseFolderDetails = () => import( /* webpackChunkName: "course" */ "@/views/Super/CourseFolderDetails.vue");
const CreateCourseFolder = () => import(/* webpackChunkName: "course" */ "@/views/Super/CreateCourseFolder.vue");
const CertificateDetails = () => import(/* webpackChunkName: "cert" */ "@/views/Super/CertificateDetails.vue");
const CreateCompany = () => import(/* webpackChunkName: "company" */ "@/views/Super/CreateCompany.vue");
const AllUsers = () => import(/* webpackChunkName: "user" */ "@/views/Super/AllUsers.vue");
const CompanyLocations = () => import(/* webpackChunkName: "location" */ "@/views/Super/CompanyLocations.vue");
const AddLocation = () => import(/* webpackChunkName: "location" */ "@/views/Super/AddLocation.vue");
const CompanyCertificates = () => import(/* webpackChunkName: "cert" */ "@/views/Super/CompanyCertificates.vue");
const CompanyCourses = () => import(/* webpackChunkName: "course" */ "@/views/Super/CompanyCourses.vue");
const CompanyCourseFolders = () => import("@/views/Super/CompanyCourseFolders.vue");
const LocationDetails = () => import(/* webpackChunkName: "location" */ "@/views/Super/LocationDetails.vue");
const CourseCatalog = () => import(/* webpackChunkName: "course" */ "@/views/Super/CourseCatalog.vue");
const CompanyCourseDetails = () => import(/* webpackChunkName: "company" */ "@/views/Super/CompanyCourseDetails.vue");
const CourseInstructions = () => import(/* webpackChunkName: "course" */ "@/views/Super/CourseInstructions.vue");
const LessonForm = () => import(/* webpackChunkName: "course" */ "@/views/Super/LessonForm.vue");
const EditCourse = () => import(/* webpackChunkName: "course" */ "@/views/Super/EditCourse.vue");
const EmployeeCertificates = () => import(/* webpackChunkName: "cert" */ "@/views/Super/EmployeeCertificates.vue");
const EmployeeResources = () => import(/* webpackChunkName: "resource" */ "@/views/Super/EmployeeResources.vue");
const EmployeeDocuments = () => import(/* webpackChunkName: "resource" */ "@/views/Super/EmployeeDocuments.vue");
const EmployeeCourses = () => import(/* webpackChunkName: "course" */ "@/views/Super/EmployeeCourses.vue");
const EmployeeCoursePurchase = () => import(/* webpackChunkName: "course" */ "@/views/Super/EmployeeCoursePurchase.vue");
const AddEmployee = () => import(/* webpackChunkName: "employee" */ "@/views/Super/AddEmployee.vue");
const CreateUser = () => import(/* webpackChunkName: "employee" */ "@/views/Super/CreateUser.vue");
const CompanyEmployees = () => import(/* webpackChunkName: "employee" */ "@/views/Super/CompanyEmployees.vue");
const Resources = () => import(/* webpackChunkName: "resources" */ "@/views/Super/Resources.vue");
const LoginReport = () => import(/* webpackChunkName: "resources" */ "@/views/Super/LoginReport.vue");
const ActivityReport = () => import(/* webpackChunkName: "resources" */ "@/views/Super/ActivityReport.vue");
const CourseFailPassReport = () => import(/* webpackChunkName: "resources" */ "@/views/Super/CourseFailPassReport.vue");
// Dashboard pages
const Dashboard = () => import(/* webpackChunkName: "dashboard" */ "@/views/Dashboard/Dashboard.vue");
// Pages
const Login = () => import(/* webpackChunkName: "pages" */ "@/views/Pages/Login.vue");
const Home = () => import(/* webpackChunkName: "pages" */ "@/views/Pages/Home.vue");
const Tour = () => import(/* webpackChunkName: "pages" */ "@/views/Pages/Tour.vue");
const Register = () => import(/* webpackChunkName: "pages" */ "@/views/Pages/Register.vue");
const RegisterMinLocation = () => import(/* webpackChunkName: "pages" */ "@/views/Pages/RegisterMinLocation.vue");
const UserRegister = () => import(/* webpackChunkName: "pages" */ "@/views/Pages/UserRegister.vue");
const Signup = () => import(/* webpackChunkName: "pages" */ "@/views/Pages/Signup.vue");
const ForgetPassword = () => import(/* webpackChunkName: "employee" */ "@/views/Super/ForgetPassword.vue");

const ResetPassword = () => import(/* webpackChunkName: "employee" */ "@/views/Super/ResetPassword.vue");
const TestQuestionReport = () => import(/* webpackChunkName: "employee" */ "@/views/Super/TestQuestionReport.vue");
const SurveyReport = () => import(/* webpackChunkName: "employee" */ "@/views/Super/SurveyReport.vue");
const SurveySubmissions = () => import(/* webpackChunkName: "employee" */ "@/views/Super/SurveySubmissions.vue");
const SurveySubmissionsDetail = () => import(/* webpackChunkName: "employee" */ "@/views/Super/SurveySubmissionsDetail.vue");
const UserOnboardingReport = () => import(/* webpackChunkName: "resources" */ "@/views/Super/UserOnboardingReport.vue");
const AllOnboardingReport = () => import (/* webpackChunkName: "resources" */ '@/views/Super/AllOnboardingReport.vue');
const HrFormReport = () => import(/* webpackChunkName: "resources" */ "@/views/Super/HrFormReport.vue");
//sub-admin
const CreateSubAdmin = () => import (/* webpackChunkName: "employee" */ '@/views/Super/CreateSubAdmin.vue');
// Promo code
const PromoCodes = () => import (/* webpackChunkName: "employee" */ '@/views/Super/PromoCodes.vue');
const CreatePromoCodes = () => import (/* webpackChunkName: "employee" */ '@/views/Super/CreatePromoCodes.vue');
const PromoCodeReport = () => import (/* webpackChunkName: "employee" */ '@/views/Super/PromoCodeReport.vue');
let authPages = {
    path: "/",
    component: AuthLayout,
    name: "Authentication",
    children: [
        {
            path: "/home",
            name: "Home",
            component: Home,
            meta: {
                noBodyBackground: true,
            },
        },
        {
            path: "/login",
            name: "Login",
            component: Login,
            meta: {
                noBodyBackground: true,
            },
        },
        {
            path: "/tour",
            name: "Tour",
            component: Tour,
            meta: {
                noBodyBackground: true,
            },
        },
        {
            path: "/forget_password",
            name: "ForgetPassword",
            component: ForgetPassword,
            meta: {
                noBodyBackground: true,
            },
        },
        {
            path: "/reset_password",
            name: "ResetPassword",
            component: ResetPassword,
            meta: {
                title: "Reset-Password",
                noBodyBackground: true,
            },
        },
        {
            path: "/register",
            name: "Register",
            component: Register,
            meta: {
                title: "Register",
                noBodyBackground: true,
            },
        },
        {
            path: "/register_1-3_locations",
            name: "RegisterMinLocation",
            component: RegisterMinLocation,
            meta: {
                noBodyBackground: true,
            },
        },
        {
            path: "/user_register",
            name: "User Register",
            component: UserRegister,
            meta: {
                title: "User-Register",
                noBodyBackground: true,
            },
        },
        {
            path: "/signup",
            name: "Sign Up",
            component: Signup,
            meta: {
                title: "Sign-Up",
                noBodyBackground: true,
            },
        },

        {path: "*", component: NotFound},
    ],
};

const routes = [
    {
        path: "/",
        redirect: "/login",
        name: "Login",
    },

    {
        path: "/",
        component: DashboardLayout,
        redirect: "/dashboard",
        name: "Dashboard",
        children: [
            {
                path: "dashboard",
                name: "Dashboard",
                component: Dashboard,
            },
            {
                path: "all_companies",
                name: "All Companies",
                component: AllCompanies,
            },
            {
                path: "edit_company",
                name: "Edit Company",
                component: EditCompany,
            },
            {
                path: "certificates",
                name: "Certificates",
                component: Certificates,
            },
            {
                path: "courses",
                name: "Courses",
                component: Courses,
            },
            {
                path: "create_course",
                name: "CreateCourse",
                component: CreateCourse,
            },
            {
                path: "course_folder",
                name: "CourseFolder",
                component: CourseFolder,
            },
            {
                path: "course_folder_details",
                name: "CourseFolderDetails",
                component: CourseFolderDetails,
            },
            {
                path: "create_course_folder",
                name: "CreateCourseFolder",
                component: CreateCourseFolder,
            },
            {
                path: "certificate_details",
                name: "CertificateDetails",
                component: CertificateDetails,
            },
            {
                path: "create_company",
                name: "CreateCompany",
                component: CreateCompany,
            },
            {
                path: "test_question_report",
                name: "TestQuestionReport",
                component: TestQuestionReport,
            },
            {
                path: "survey_report",
                name: "SurveyReport",
                component: SurveyReport,
            },
            {
                path: "survey_submissions",
                name: "SurveySubmissions",
                component: SurveySubmissions,
            },
            {
                path: "survey_submissions_detail",
                name: "SurveySubmissionsDetail",
                component: SurveySubmissionsDetail,
            },
            {
                path: "account",
                name: "Account",
                component: Account,
            },
            {
                path: "tutorial_video",
                name: "TutorialVideo",
                component: TutorialVideo,
            },
            {
                path: "tour_page",
                name: "TourPage",
                component: TourPage,
            },
            {
                path: "add_tutorial_video",
                name: "AddTutorialVideo",
                component: AddTutorialVideo,
            },
            {
                path: "add_tour_video",
                name: "AddTourVideo",
                component: AddTourVideo,
            },
            {
                path: "create_certificate",
                name: "CreateCertificate",
                component: CreateCertificate,
            },
            {
                path: "all_users",
                name: "All Users",
                component: AllUsers,
            },
            {
                path: "create_user",
                name: "Create Users",
                component: CreateUser,
            },
            {
                path: "company_locations",
                name: "ComapanyLocations",
                component: CompanyLocations,
            },
            {
                path: "add_location",
                name: "AddLocation",
                component: AddLocation,
            },
            {
                path: "company_certificates",
                name: "CompanyCertificates",
                component: CompanyCertificates,
            },
            {
                path: "company_courses",
                name: "CompanyCourses",
                component: CompanyCourses,
            },
            {
                path: "location_details",
                name: "LocationDetails",
                component: LocationDetails,
            },
	        {
		        path: "company_coursefolders",
		        name: "CompanyCourseFolders",
		        component: CompanyCourseFolders
	        },
            {
                path: "course_catalog",
                name: "CourseCatalog",
                component: CourseCatalog,
            },
            {
                path: "company_course_details",
                name: "CompanyCourseDetails",
                component: CompanyCourseDetails,
            },
            {
                path: "course_instructions",
                name: "CourseInstructions",
                component: CourseInstructions,
            },
            {
                path: "lesson_form",
                name: "LessonForm",
                component: LessonForm,
            },
            {
                path: "edit_course",
                name: "EditCourse",
                component: EditCourse,
            },
            {
                path: "employee_certificates",
                name: "EmployeeCertificates",
                component: EmployeeCertificates,
            },
            {
                path: "employee_resources",
                name: "EmployeeResources",
                component: EmployeeResources,
            },
            {
                path: "employee_documents",
                name: "EmployeeDocuments",
                component: EmployeeDocuments,
            },

            {
                path: "employee_courses",
                name: "EmployeeCourses",
                component: EmployeeCourses,
            },
	        {
		        path: "employee_course_purchase",
		        name: "EmployeeCoursePurchase",
		        component: EmployeeCoursePurchase,
	        },
            {
                path: "/add_employee",
                name: "AddEmployee",
                component: AddEmployee,
            },
            {
                path: "/company_employees",
                name: "CompanyEmployees",
                component: CompanyEmployees,
            },
            {
                path: "/resources",
                name: "Resources",
                component: Resources,
            },
            {
                path: "/login_report",
                name: "LoginReport",
                component: LoginReport,
            },
            {
                path: "/activity_report",
                name: "ActivityReport",
                component: ActivityReport,
            },
            {
                path: "/documents-report",
                name: "Documents Report",
                component: () => import("@/views/Common/DocumentsReport.vue"),
            },
            {
                path: "/course_fail_pass_report",
                name: "CourseFailPassReport",
                component: CourseFailPassReport,
            },
            {
                path: "/onboarding_report",
                name: "OnboardingReport",
                component: UserOnboardingReport,
            },
            {
                path: '/all_onboarding_report',
                name: 'AllOnboardingReport',
                component: AllOnboardingReport
            },
            {
                path: "/hrform_report",
                name: "HrFromReport",
                component: HrFormReport,
            },
            {
                path: '/create_subadmin',
                name: 'CreateSubAdmin',
                component: CreateSubAdmin
            },
            {
                path: '/promo_codes',
                name: 'PromoCodes',
                component: PromoCodes
            },
            {
                path: '/create_promo_code',
                name: 'CreatePromoCodes',
                component: CreatePromoCodes
            },
            {
                path: '/promo_code_report',
                name: 'PromoCodeReport',
                component: PromoCodeReport
            },

        ],
    },
    authPages,
];

export default routes;

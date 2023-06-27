const DynamicName = process.env.VUE_APP_COMPANY_NAME;
 const TRAIN321 = {
    'SITE_NAME': 'Train 321',
    'SITE_URL': 'www.train321.com',
    'LOGO': 'Train_321.png',
    'INFO_EMAIL': 'info@train321.com',
	'SUPPORT_EMAIL': 'support@train321.com'
}
 const Home_Of_Training  = {
    'SITE_NAME': 'Home of Training',
    'SITE_URL': 'www.homeoftraining.com',
    'LOGO': 'Homeoftraining.png',
    'INFO_EMAIL': 'info@homeoftraining.com',
	'SUPPORT_EMAIL': 'support@homeoftraining.com'
}
let Dynamic = {};
switch (DynamicName) { 
    case "TRAIN321":
        Dynamic[DynamicName] = TRAIN321;
        break;
    case "Home_Of_Training":
        Dynamic[DynamicName] = Home_Of_Training;
        break;
    default:
       Dynamic["TRAIN321"] = TRAIN321;  
}
let DName = Dynamic[DynamicName];
export  { DName as Dynamic }  ;

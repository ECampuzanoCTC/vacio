

  function dateToRelative(localTime){
    var diff=new Date().getTime()-localTime;
    var ret="";

    var min=60000;
    var hour=3600000;
    var day=86400000;
    var wee=604800000;
    var mon=2629800000;
    var yea=31557600000;

    if (diff<-yea*2)
      ret ="in ## years".replace("##",(-diff/yea).toFixed(0));

    else if (diff<-mon*9)
      ret ="in ## months".replace("##",(-diff/mon).toFixed(0));

    else if (diff<-wee*5)
      ret ="in ## weeks".replace("##",(-diff/wee).toFixed(0));

    else if (diff<-day*2)
      ret ="in ## days".replace("##",(-diff/day).toFixed(0));

    else if (diff<-hour)
      ret ="in ## hours".replace("##",(-diff/hour).toFixed(0));

    else if (diff<-min*35)
      ret ="in about one hour";

    else if (diff<-min*25)
      ret ="in about half hour";

    else if (diff<-min*10)
      ret ="in some minutes";

    else if (diff<-min*2)
      ret ="in few minutes";

    else if (diff<=min)
      ret ="just now";

    else if (diff<=min*5)
      ret ="few minutes ago";

    else if (diff<=min*15)
      ret ="some minutes ago";

    else if (diff<=min*35)
      ret ="about half hour ago";

    else if (diff<=min*75)
      ret ="about an hour ago";

    else if (diff<=hour*5)
      ret ="few hours ago";

    else if (diff<=hour*24)
      ret ="## hours ago".replace("##",(diff/hour).toFixed(0));

    else if (diff<=day*7)
      ret ="## days ago".replace("##",(diff/day).toFixed(0));

    else if (diff<=wee*5)
      ret ="## weeks ago".replace("##",(diff/wee).toFixed(0));

    else if (diff<=mon*12)
      ret ="## months ago".replace("##",(diff/mon).toFixed(0));

    else
      ret ="## years ago".replace("##",(diff/yea).toFixed(0));

    return ret;
  }

  //override date format i18n
  
  Date.monthNames = ["January","February","March","April","May","June","July","August","September","October","November","December"];
  // Month abbreviations. Change this for local month names
  Date.monthAbbreviations = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
  // Full day names. Change this for local month names
  Date.dayNames =["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
  // Day abbreviations. Change this for local month names
  Date.dayAbbreviations = ["Sun","Mon","Tue","Wed","Thu","Fri","Sat"];
  // Used for parsing ambiguous dates like 1/2/2000 - default to preferring 'American' format meaning Jan 2.
  // Set to false to prefer 'European' format meaning Feb 1
  Date.preferAmericanFormat = false;

  Date.firstDayOfWeek =0;
  Date.defaultFormat = "M/d/yyyy";
  Date.masks = {
        fullDate:       "EEEE, MMMM d, yyyy",
        shortTime:      "h:mm a"
  };

  Number.decimalSeparator = ".";
  Number.groupingSeparator = ",";
  Number.minusSign = "-";
  Number.currencyFormat = "###,##0.00";



  var millisInWorkingDay =28800000;
  var workingDaysPerWeek =5;

  function isHoliday(date) {
    var friIsHoly =false;
    var satIsHoly =false;
    var sunIsHoly =false;

    pad = function (val) {
      val = "0" + val;
      return val.substr(val.length - 2);
    };

    var holidays = "##";

    var ymd = "#" + date.getFullYear() + "_" + pad(date.getMonth() + 1) + "_" + pad(date.getDate()) + "#";
    var md = "#" + pad(date.getMonth() + 1) + "_" + pad(date.getDate()) + "#";
    var day = date.getDay();

    return  (day == 5 && friIsHoly) || (day == 6 && satIsHoly) || (day == 0 && sunIsHoly) || holidays.indexOf(ymd) > -1 || holidays.indexOf(md) > -1;
  }

function isHolidayView(date) {
    var friIsHoly =false;
    var satIsHoly =true;
    var sunIsHoly =true;

    pad = function (val) {
      val = "0" + val;
      return val.substr(val.length - 2);
    };

    var holidays = "##";

    var ymd = "#" + date.getFullYear() + "_" + pad(date.getMonth() + 1) + "_" + pad(date.getDate()) + "#";
    var md = "#" + pad(date.getMonth() + 1) + "_" + pad(date.getDate()) + "#";
    var day = date.getDay();

    return  (day == 5 && friIsHoly) || (day == 6 && satIsHoly) || (day == 0 && sunIsHoly) || holidays.indexOf(ymd) > -1 || holidays.indexOf(md) > -1;
  }

  
  var i18n = {
    YES:                 "yes",
    NO:                  "no",
    FLD_CONFIRM_DELETE:  "confirm the deletion?",
    INVALID_DATA:        "The data inserted are invalid for the field format.",
    ERROR_ON_FIELD:      "Error on field",
    OUT_OF_BOUDARIES:      "Out of field admitted values:",
    CLOSE_ALL_CONTAINERS:"close all?",
    DO_YOU_CONFIRM:      "Do you confirm?",
    ERR_FIELD_MAX_SIZE_EXCEEDED:      "Field max size exceeded",
    
    FILE_TYPE_NOT_ALLOWED:"File type not allowed.",
    FILE_UPLOAD_COMPLETED:"File upload completed.",
    UPLOAD_MAX_SIZE_EXCEEDED:"Max file size exceeded",
    ERROR_UPLOADING:"Error uploading",
    UPLOAD_ABORTED:"Upload aborted",
    DROP_HERE:"Drop files here",

    FORM_IS_CHANGED:     "You have some unsaved data on the page!"
  };

  
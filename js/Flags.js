var Flags = (function($){

    var instance;
  
    function Flags(){
        let self = this;
  
        self.DB_PARAM_ERROR                = '1000';

        self.DB_SAVE_SUCCESS               = '1001';
    
        self.DB_SAVE_EXCEPTION             = '1002';
    
        self.DB_SAVE_ERROR                 = '1003';
    
        self.DB_UPDATE_ERROR               = '1004';
    
        self.DB_UPDATE_NO_ROWS             = '1005';

        self.DB_DELETE_ERROR               = '1006';

        self.DB_DELETE_SUCCESS             = '1007';
    
        self.DB_DELETE_NO_ROWS             = '1008';
    
        self.UPLOAD_SUCCESS                = '2000';
    
        self.COPY_FILE_ERROR               = '2001';
    
        self.CREATE_DIR_ERROR              = '2004';
    
        self.ASSIGN_PERMISSON_SUCCESS      = '3000';
    
        self.ASSIGN_DELETE_ERROR           = '3001';
    
        self.ASSIGN_ADD_ERROR              = '3002';
    
        self.ASSIGN_DEFUALT_ERROR          = '3003';
  
    }
  
    return {
      getInstance: function(){
        if (!instance)
          instance = new Flags();
        return instance;
      }
    }
  
  })(jQuery);
  
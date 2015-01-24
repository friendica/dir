(function(){
    
    window.Smoothing = {
        
        /**
         * Applies both a moving average bracket and and exponential smoothing.
         * @param  {array}  raw     The raw Y values.
         * @param  {float}  factor  The exponential smoothing factor to apply (between o and 1).
         * @param  {int}    bracket The amount of datapoints to add to the backet on each side! (2 = 5 data points)
         * @return {array}          The smoothed Y values.
         */
        exponentialMovingAverage: function(raw, factor, bracket){
            
            var output = [];
            var smoother = new ExponentialSmoother(factor);
            
            //Transform each data point with the smoother.
            for (var i = 0; i < raw.length; i++){
                
                var input = raw[i];
                
                //See if we should bracket.
                if(bracket > 0){
                    
                    //Cap our start and end so it doesn't go out of bounds.
                    var start = Math.max(i-bracket, 0);
                    var end = Math.min(i+bracket, raw.length);
                    
                    //Push the range to our input.
                    input = [];
                    for(var j = start; j < end; j++){
                        input.push(raw[j]);
                    }
                    
                }
                
                output.push(
                    smoother.transform(input)
                );
            };
            
            return output;
            
        }
        
    };
    
    // Exponential Smoother class.
    var ExponentialSmoother = function(factor){
        this.currentValue = null;
        this.smoothingFactor = factor || 1;
    };
    
    ExponentialSmoother.prototype.transform = function(input){
        
        // In case our input is a bracket, first average it.
        if(input.length){
            var len = input.length;
            var sum = 0;
            for (var i = input.length - 1; i >= 0; i--)
                sum += input[i]
            input = sum/len;
        }
        
        // Start with our initial value.
        if(this.currentValue === null){
            this.currentValue = input;
        }
        
        // Our output is basically an updated value.
        return this.currentValue = 
            
            // Weigh our current value with the smoothing factor.
            (this.currentValue * this.smoothingFactor) +
            
            // Add the input to it with the inverse value of the smoothing factor.
            ( (1-this.smoothingFactor) * input );
        
    };
    
})();
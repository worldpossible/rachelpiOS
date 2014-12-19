/***************************************/
/* Simple Fraction Calculator          */
/* Copyright MathExpression.com        */
/* http://www.mathexpression.com       */
/* By Ng Wei Chong                     */
/***************************************/

  //globals
  var N1, N2, D1, D2, OP;
  var resultNumer, resultDenom;
  var szTable = new Array(10);
  var nTableCount = 0;
  var isNegative = new Boolean(false);
  
function startCalc()
{ 
   //clear the alert message;
   document.getElementById('alertMessage').innerHTML ='';
	
	
	//ensure only numbers are entered
	if(isNaN(document.fractionCalc.N1.value) || isNaN(document.fractionCalc.D1.value) || isNaN(document.fractionCalc.N2.value) || isNaN(document.fractionCalc.D2.value))
	   {
	   		document.getElementById('alertMessage').innerHTML ='*Please enter numbers only';
	   		return;
	   }
	
	//make sure no fields are left blank
	if(document.fractionCalc.N1.value == '' || document.fractionCalc.D1.value == '' || document.fractionCalc.N2.value == '' || document.fractionCalc.D2.value == '' )
		{
			document.getElementById('alertMessage').innerHTML ='*Please enter numbers into the empty box(s)';
			return;
		}
			
   //make sure no fields are > 0 or !=0
	if(document.fractionCalc.N1.value < 0 || document.fractionCalc.N2.value < 0)
		{
			
			document.getElementById('alertMessage').innerHTML ='*The numerators can only accept numbers from 0 - 99';
			return;
		}
  	
  	if(document.fractionCalc.D1.value <= 0 || document.fractionCalc.D2.value <= 0)
		{
			document.getElementById('alertMessage').innerHTML ='*The denominators can only accept numbers from 1 - 99';
			return;
		}   
		
		
  //clear the alert message;
  document.getElementById('alertMessage').innerHTML ='';
  
  //init
  szTable[0]= szTable[1] = szTable[2] = szTable[3] = szTable[4] = szTable[5] = szTable[6] = szTable[7] = szTable[8] = szTable[9] = '';
  nTableCount = 0;
  isNegative = false;
	
	
	//Now get the numbers	
	N1 = document.fractionCalc.N1.value;
	N2 = document.fractionCalc.N2.value;
	D1 = document.fractionCalc.D1.value;
	D2 = document.fractionCalc.D2.value;
    OP = document.fractionCalc.OP.value;

    //alert(OP);
    switch(OP)
    {//sw
      case '+':
      	 AddFractions();
      break;
      
      case '-':
         SubtractFractions();
      break;
      
      case '*':
        MultiplyFractions();
       break;
      
      case '/':
      	DivideFractions();
       break;
       
    
    }//sw
    
}


function AddFractions()
{
  
  if(D1 == D2)
  {
  	
  	resultNumer = (N1*1) + (N2*1);
  	resultDenom = D1*1;
  	
  	
  	
  	szTable[nTableCount] = '<table id="'+ 'TableStep' + (nTableCount + 1) + '" style="text-align:center;font-size:large" border="0" cellpadding="0" cellspacing="0"><tr><td>' + N1 + '</td><td rowspan="2">+</td><td>' + N2 + '</td><td style="font-size:x-small; padding-left:100px; text-align:left; color:black;" rowspan="2"><blockquote style="background-color: rgb(255, 255, 221); margin:5px; text-align:center"><a href="http://www.mathexpression.com/adding-fractions.html">Click here</a> to learn the basics behind adding fractions</blockquote></td></tr><tr><td style="border-top: 2px solid rgb(0, 0, 0);">' + D1 + '</td><td style="border-top: 2px solid rgb(0, 0, 0);">' + D2 + '</td></table>';
  	nTableCount++;
  	

  	szTable[nTableCount] = '<table id="'+ 'TableStep' + (nTableCount + 1) + '" style="text-align:center;font-size:large" border="0" cellpadding="0" cellspacing="0"><tr><td rowspan="2">=</td><td> ' + N1 + ' </td><td>+</td><td> ' + N2 + ' </td><td style="font-size:x-small; padding-left:20px; text-align:left; color:maroon" rowspan="2">Same denominators. Hence, add the numerators and keep the denominator the same. </td></tr><tr><td colspan="3" style="border-top: 2px solid rgb(0, 0, 0);"> ' + D1 + ' </td></tr></table>';
	nTableCount++;
	

	szTable[nTableCount] = '<table id="'+ 'TableStep' + (nTableCount + 1) + '" style="text-align:center;font-size:large" border="0" cellpadding="0" cellspacing="0"><tr><td rowspan="2">=</td><td>' + resultNumer + '</td><td>&nbsp;</td><td style="font-size:small;" rowspan="2">&nbsp;</td></tr><tr><td style="border-top: 2px solid rgb(0, 0, 0);">' + D1 +'</td><td>&nbsp;</td></table>';
    nTableCount++;
    
  }
  
  if(D1 != D2)
  {
  	
   	resultNumer = (N1*D2) + (N2*D1);
  	resultDenom = D1*D2;
	
  	szTable[nTableCount] = '<table id="'+ 'TableStep' + (nTableCount + 1) + '" style="text-align:center;font-size:large" border="0" cellpadding="0" cellspacing="0"><tr><td>' + N1 + '</td><td rowspan="2">+</td><td>' + N2 + '</td><td style="font-size:x-small; padding-left:100px; text-align:left; color:black;" rowspan="2"><blockquote style="background-color: rgb(255, 255, 221); margin:5px; text-align:center"><a href="http://www.mathexpression.com/adding-fractions.html">Click here</a> to learn the basics behind adding fractions</blockquote></td></tr><tr><td style="border-top: 2px solid rgb(0, 0, 0);">' + D1 + '</td><td style="border-top: 2px solid rgb(0, 0, 0);">' + D2 + '</td></table>';
  	nTableCount++;
  		
	szTable[nTableCount] = '<table id="'+ 'TableStep' + (nTableCount + 1) + '" style="text-align:center;font-size:large" border="0" cellpadding="0" cellspacing="0"> 	\
								<tr> 											\
									<td rowspan="2">=</td>  					\
									<td> '+ N1 + '</td>							\
									<td style="font-size:medium"><sup><b style="color:green">&times;'+ D2 + '</b></sup></td>					\
									<td rowspan="2">+</td>						\
									<td> '+ N2 +' </td>							\
									<td style="font-size:medium"><sup><b style="color:green">&times;' + D1 + '</b></sup></td>					\
									<td style="font-size:x-small; padding-left:20px; text-align:left; color:maroon" rowspan="2">Different denominators, use the ideas behind <a href="http://www.mathexpression.com/equivalent-fractions.html">Equivalent Fractions</a> to make them the same</td>				\
								</tr>											\
								<tr>											\
									<td style="border-top: 2px solid rgb(0, 0, 0)">' + D1 + '</td>									\
									<td style="font-size:medium"><sup><b style="color:green">&times;'+ D2 + '</b></sup></td>					\
									<td style="border-top: 2px solid rgb(0, 0, 0)">' + D2 + '</td>									\
									<td style="font-size:medium"><sup><b style="color:green">&times;'+ D1 + '</b></sup></td>					\
								</tr>											\
						    </table>'

	nTableCount++;
	
	
	  	
  	szTable[nTableCount] = '<table id="'+ 'TableStep' + (nTableCount + 1) + '" style="text-align:center;font-size:large" border="0" cellpadding="0" cellspacing="0"><tr><td rowspan="2">=</td><td>' + (N1*D2) + '</td><td rowspan="2">+</td><td>' + (N2*D1) + '</td><td style="font-size:x-small; padding-left:20px; text-align:left; color:maroon" rowspan="2">The denominators are now the same</td></tr><tr><td style="border-top: 2px solid rgb(0, 0, 0);">' + (D1*D2) + '</td><td style="border-top: 2px solid rgb(0, 0, 0);">' + (D1*D2) + '</td></table>';
  	nTableCount++;

	
	szTable[nTableCount] = '<table id="'+ 'TableStep' + (nTableCount + 1) + '" style="text-align:center;font-size:large" border="0" cellpadding="0" cellspacing="0"><tr><td rowspan="2">=</td><td> ' + (N1*D2) + ' </td><td>+</td><td> ' + (N2*D1) + ' </td><td style="font-size:x-small; padding-left:20px; text-align:left; color:maroon" rowspan="2">Hence, we can just add the numerators and keep the denominator the same</td></tr><tr><td colspan="3" style="border-top: 2px solid rgb(0, 0, 0);"> ' + (D1*D2) + ' </td></tr></table>';
	nTableCount++;

	
	
	szTable[nTableCount] = '<table id="'+ 'TableStep' + (nTableCount + 1) + '" style="text-align:center;font-size:large" border="0" cellpadding="0" cellspacing="0"><tr><td rowspan="2">=</td><td>' + resultNumer + '</td><td>&nbsp;</td><td style="font-size:small;" rowspan="2">&nbsp;</td></tr><tr><td style="border-top: 2px solid rgb(0, 0, 0);">' + resultDenom +'</td><td>&nbsp;</td></table>';
    nTableCount++;
    
  }

  //alert(nTableCount);
  SimplifyFraction();
  UpdateDocument();

}


function SubtractFractions()
{
  
  if(D1 == D2)
  {
  	
  	resultNumer = (N1*1) - (N2*1);
  	resultDenom = D1*1;

  	 if(resultNumer <0)
  	 {
  		isNegative = true;
	 	resultNumer = Math.abs(resultNumer);
	 }
	 
  	szTable[nTableCount] = '<table id="'+ 'TableStep' + (nTableCount + 1) + '" style="text-align:center;font-size:large" border="0" cellpadding="0" cellspacing="0"><tr><td>' + N1 + '</td><td rowspan="2">-</td><td>' + N2 + '</td><td style="font-size:x-small; padding-left:100px; text-align:left; color:black;" rowspan="2"><blockquote style="background-color: rgb(255, 255, 221); margin:5px; text-align:center"><a href="http://www.mathexpression.com/subtracting-fractions.html">Click here</a> to learn the basics behind subtracting fractions</blockquote></td></tr><tr><td style="border-top: 2px solid rgb(0, 0, 0);">' + D1 + '</td><td style="border-top: 2px solid rgb(0, 0, 0);">' + D2 + '</td></table>';
  	nTableCount++;
  	

  	szTable[nTableCount] = '<table id="'+ 'TableStep' + (nTableCount + 1) + '" style="text-align:center;font-size:large" border="0" cellpadding="0" cellspacing="0"><tr><td rowspan="2">=</td><td> ' + N1 + ' </td><td>-</td><td> ' + N2 + ' </td><td style="font-size:x-small; padding-left:20px; text-align:left; color:maroon" rowspan="2">Same denominators. Hence, add the numerators and keep the denominator the same.</td></tr><tr><td colspan="3" style="border-top: 2px solid rgb(0, 0, 0);"> ' + D1 + ' </td></tr></table>';
	nTableCount++;
	
	
	szTable[nTableCount] = '<table id="'+ 'TableStep' + (nTableCount + 1) + '" style="text-align:center;font-size:large" border="0" cellpadding="0" cellspacing="0"><tr><td rowspan="2">'+ (szTemp=(isNegative==true)?'=&#45;':'=') +'</td><td>' + Math.abs(resultNumer) + '</td><td>&nbsp;</td><td style="font-size:small;" rowspan="2">&nbsp;</td></tr><tr><td style="border-top: 2px solid rgb(0, 0, 0);">' + D1 +'</td><td>&nbsp;</td></table>';
    nTableCount++;
    
  }
  
  if(D1 != D2)
  {
  	
   	resultNumer = (N1*D2) - (N2*D1);
  	resultDenom = D1*D2;
  	
  	
  	 if(resultNumer <0)
  	 {
  		isNegative = true;
	 	resultNumer = Math.abs(resultNumer);
	 }

	
  	szTable[nTableCount] = '<table id="'+ 'TableStep' + (nTableCount + 1) + '" style="text-align:center;font-size:large" border="0" cellpadding="0" cellspacing="0"><tr><td>' + N1 + '</td><td rowspan="2">-</td><td>' + N2 + '</td><td style="font-size:x-small; padding-left:100px; text-align:left; color:black;" rowspan="2"><blockquote style="background-color: rgb(255, 255, 221); margin:5px; text-align:center"><a href="http://www.mathexpression.com/subtracting-fractions.html">Click here</a> to learn the basics behind subtracting fractions</blockquote></td></tr><tr><td style="border-top: 2px solid rgb(0, 0, 0);">' + D1 + '</td><td style="border-top: 2px solid rgb(0, 0, 0);">' + D2 + '</td></table>';
  	nTableCount++;
  		
	szTable[nTableCount] = '<table id="'+ 'TableStep' + (nTableCount + 1) + '" style="text-align:center;font-size:large" border="0" cellpadding="0" cellspacing="0"> 	\
								<tr> 											\
									<td rowspan="2">=</td>  					\
									<td> '+ N1 + '</td>							\
									<td style="font-size:medium"><sup><b style="color:green">&times;'+ D2 + '</b></sup></td>					\
									<td rowspan="2">-</td>						\
									<td> '+ N2 +' </td>							\
									<td style="font-size:medium"><sup><b style="color:green">&times;' + D1 + '</b></sup></td>					\
									<td style="font-size:x-small; padding-left:20px; text-align:left; color:maroon" rowspan="2">Different denominators, use the ideas behind <a href="http://www.mathexpression.com/equivalent-fractions.html">Equivalent Fractions</a> to make them the same</td>				\
								</tr>											\
								<tr>											\
									<td style="border-top: 2px solid rgb(0, 0, 0)">' + D1 + '</td>									\
									<td style="font-size:medium"><sup><b style="color:green">&times;'+ D2 + '</b></sup></td>					\
									<td style="border-top: 2px solid rgb(0, 0, 0)">' + D2 + '</td>									\
									<td style="font-size:medium"><sup><b style="color:green">&times;'+ D1 + '</b></sup></td>					\
								</tr>											\
						    </table>'

	nTableCount++;
	
	
	  	
  	szTable[nTableCount] = '<table id="'+ 'TableStep' + (nTableCount + 1) + '" style="text-align:center;font-size:large" border="0" cellpadding="0" cellspacing="0"><tr><td rowspan="2">=</td><td>' + (N1*D2) + '</td><td rowspan="2">-</td><td>' + (N2*D1) + '</td><td style="font-size:x-small; padding-left:20px; text-align:left; color:maroon" rowspan="2">The denominators are now the same</td></tr><tr><td style="border-top: 2px solid rgb(0, 0, 0);">' + (D1*D2) + '</td><td style="border-top: 2px solid rgb(0, 0, 0);">' + (D1*D2) + '</td></table>';
  	nTableCount++;

	
	szTable[nTableCount] = '<table id="'+ 'TableStep' + (nTableCount + 1) + '" style="text-align:center;font-size:large" border="0" cellpadding="0" cellspacing="0"><tr><td rowspan="2">=</td><td> ' + (N1*D2) + ' </td><td>-</td><td> ' + (N2*D1) + ' </td><td style="font-size:x-small; padding-left:20px; text-align:left; color:maroon" rowspan="2">Hence, we can just minus the numerators and keep the denominator the same</td></tr><tr><td colspan="3" style="border-top: 2px solid rgb(0, 0, 0);"> ' + (D1*D2) + ' </td></tr></table>';
	nTableCount++;

	
	szTable[nTableCount] = '<table id="'+ 'TableStep' + (nTableCount + 1) + '" style="text-align:center;font-size:large" border="0" cellpadding="0" cellspacing="0"><tr><td rowspan="2">'+ (szTemp=(isNegative==true)?'=&#45;':'=') +'</td><td>' + resultNumer + '</td><td>&nbsp;</td><td style="font-size:small;" rowspan="2">&nbsp;</td></tr><tr><td style="border-top: 2px solid rgb(0, 0, 0);">' + resultDenom +'</td><td>&nbsp;</td></table>';
    nTableCount++;
    
  }

  //alert(nTableCount);
  SimplifyFraction();
  UpdateDocument();


}

function MultiplyFractions()
{
  
   	resultNumer = (N1*N2);
  	resultDenom = (D1*D2);
  	
 
  	szTable[nTableCount] = '<table id="'+ 'TableStep' + (nTableCount + 1) + '" style="text-align:center;font-size:large" border="0" cellpadding="0" cellspacing="0"><tr><td>' + N1 + '</td><td rowspan="2">&times;</td><td>' + N2 + '</td><td style="font-size:x-small; padding-left:100px; text-align:left; color:black;" rowspan="2"><blockquote style="background-color: rgb(255, 255, 221); margin:5px; text-align:center"><a href="http://www.mathexpression.com/multiplying-fractions.html">Click here</a> to learn the basics behind multiplying fractions</blockquote></td></tr><tr><td style="border-top: 2px solid rgb(0, 0, 0);">' + D1 + '</td><td style="border-top: 2px solid rgb(0, 0, 0);">' + D2 + '</td></table>';
  	nTableCount++;
  	
	szTable[nTableCount] =	'<table id="'+ 'TableStep' + (nTableCount + 1) + '" style="text-align:center;font-size:large" border="0" cellpadding="0" cellspacing="0"> \
							<tr>										\
								<td rowspan="2">=</td> 				    \
								<td>'+ N1 + '&times;' + N2 +'</td>					\
								<td style="font-size:x-small; padding-left:20px; text-align:left; color:maroon" rowspan="2">Multiply the numerators together and multiply the denominators together.</td> 			\
							</tr>										\
							<tr>										\
								<td style="border-top: 2px solid rgb(0, 0, 0);">' + D1 + '&times;' + D2 + '</td>	\
							</tr>		\
							</table>'
	nTableCount++;
	

    szTable[nTableCount] = '<table id="'+ 'TableStep' + (nTableCount + 1) + '" style="text-align:center;font-size:large" border="0" cellpadding="0" cellspacing="0"><tr><td rowspan="2">=</td><td>' + resultNumer + '</td><td>&nbsp;</td><td style="font-size:small;" rowspan="2">&nbsp;</td></tr><tr><td style="border-top: 2px solid rgb(0, 0, 0);">' + resultDenom +'</td><td>&nbsp;</td></table>';
    nTableCount++;
    
  //alert(nTableCount);
  SimplifyFraction();
  UpdateDocument();

}


function DivideFractions()
{
  
   	resultNumer = (N1*D2);
  	resultDenom = (D1*N2);
  	
 
  	szTable[nTableCount] = '<table id="'+ 'TableStep' + (nTableCount + 1) + '" style="text-align:center;font-size:large" border="0" cellpadding="0" cellspacing="0"><tr><td>' + N1 + '</td><td rowspan="2">&divide;</td><td>' + N2 + '</td><td style="font-size:x-small; padding-left:100px; text-align:left; color:black;" rowspan="2"><blockquote style="background-color: rgb(255, 255, 221); margin:5px; text-align:center"><a href="http://www.mathexpression.com/dividing-fractions.html">Click here</a> to learn the basics behind dividing fractions</blockquote></td></tr><tr><td style="border-top: 2px solid rgb(0, 0, 0);">' + D1 + '</td><td style="border-top: 2px solid rgb(0, 0, 0);">' + D2 + '</td></table>';
  	nTableCount++;
  	
  	szTable[nTableCount] = '<table id="'+ 'TableStep' + (nTableCount + 1) + '" style="text-align:center;font-size:large" border="0" cellpadding="0" cellspacing="0"><tr><td rowspan="2">=</td><td>' + N1 + '</td><td rowspan="2">&times;</td><td>' + D2 + '</td><td style="font-size:x-small; padding-left:20px; text-align:left; color:maroon" rowspan="2">Change the division to multiplication by swapping the numerator and denominator of the divisor</td></tr><tr><td style="border-top: 2px solid rgb(0, 0, 0);">' + D1 + '</td><td style="border-top: 2px solid rgb(0, 0, 0);">' + N2 + '</td></table>';	
	nTableCount++;
	
	szTable[nTableCount] =	'<table id="'+ 'TableStep' + (nTableCount + 1) + '" style="text-align:center;font-size:large" border="0" cellpadding="0" cellspacing="0"> \
							<tr>										\
								<td rowspan="2">=</td> 				    \
								<td>'+ N1 + '&times;' + D2 +'</td>					\
								<td style="font-size:x-small; padding-left:20px; text-align:left; color:maroon" rowspan="2">Multiply the numerators together and multiply the denominators together.</td> 			\
							</tr>										\
							<tr>										\
								<td style="border-top: 2px solid rgb(0, 0, 0);">' + D1 + '&times;' + N2 + '</td>	\
							</tr>		\
							</table>'
	nTableCount++;
	

    szTable[nTableCount] = '<table id="'+ 'TableStep' + (nTableCount + 1) + '" style="text-align:center;font-size:large" border="0" cellpadding="0" cellspacing="0"><tr><td rowspan="2">=</td><td>' + resultNumer + '</td><td>&nbsp;</td><td style="font-size:small;" rowspan="2">&nbsp;</td></tr><tr><td style="border-top: 2px solid rgb(0, 0, 0);">' + resultDenom +'</td><td>&nbsp;</td></table>';
    nTableCount++;
    
  //alert(nTableCount);
  SimplifyFraction();
  UpdateDocument();

}




function GetFactor(nNumer,nDenom)
{
  //Find the largest common factors
  var nFactor = 1;
  
  nNumber = Math.abs(nNumer);
  nDenom  = Math.abs(nDenom);
   
  for (var nCount = 2; nCount <= Math.min(nNumer,nDenom); nCount++) 
  {//for
    var TestA = nNumer/nCount;   
    
    if (TestA == Math.round( TestA )) 
    {//if1
      var TestB = nDenom/nCount;
      
      if (TestB == Math.round( TestB )) 
      {//if2
        nFactor = nCount;
      }//if2
    }//if1
  }//for

return nFactor;
}


function SimplifyFraction()
{

 if( ((resultNumer >= resultDenom) && (resultNumer%resultDenom == 0)) || (resultNumer == 0))
 {
   var nWholeNum = resultNumer/resultDenom;
  
 		szTable[nTableCount] = '<table id="'+ 'TableStep' + (nTableCount + 1) + '" style="text-align:center;font-size:large" border="0" cellpadding="0" cellspacing="0"><tr><td>'+ (szTemp=(isNegative==true)?'=&#45;':'=') +'</td><td>' + nWholeNum + '</td></tr></table>';
 		 nTableCount++;
 } 

 else
 {//else
 
		 		 
		 var lFactor = GetFactor(resultNumer,resultDenom);
		 
		 
		 if(lFactor > 1)
		 {//
			 
		 szTable[nTableCount] = '<table id="'+ 'TableStep' + (nTableCount + 1) + '" style="text-align:center;font-size:large" border="0" cellpadding="0" cellspacing="0"><tr><td rowspan="2">'+ (szTemp=(isNegative==true)?'=&#45;':'=') +'</td><td>' + resultNumer + '</td><td style="font-size:medium"><sup><b style="color:green">&divide;'+ lFactor + '</b></sup></td><td style="font-size:x-small; padding-left:20px; text-align:left; color:maroon" rowspan="2">We can simplify this fraction by dividing the numerator and denominator with <b style="color:green">' + lFactor +'</b></td></tr><tr><td style="border-top: 2px solid rgb(0, 0, 0);">' + resultDenom + '</td><td style="font-size:medium"><sup><b style="color:green">&divide;' + lFactor + '</b></sup></td></tr></table>';
		 nTableCount++;	 
		 //divide with the common factor   	
		 resultNumer = resultNumer/lFactor;
		 resultDenom = resultDenom/lFactor;
		
		 szTable[nTableCount] = '<table id="'+ 'TableStep' + (nTableCount + 1) + '" style="text-align:center;font-size:large" border="0" cellpadding="0" cellspacing="0"><tr><td rowspan="2">'+ (szTemp=(isNegative==true)?'=&#45;':'=') +'</td><td>' + resultNumer + '</td><td>&nbsp;</td><td style="font-size:x-small; padding-left:20px; text-align:left; color:maroon" rowspan="2">'+ (szTemp=(resultNumer>resultDenom)? 'This is an improper fraction. Rather than leaving it like this, it is better to convert it into a mixed fraction':'&nbsp;') + '</td></tr><tr><td style="border-top: 2px solid rgb(0, 0, 0)">' + resultDenom +'</td><td>&nbsp;</td></table>';
		 nTableCount++;	
		 }
		 
		 if( (resultNumer >= resultDenom) && (resultNumer%resultDenom != 0) )
		 {//
		   var nNum = (resultNumer - resultNumer%resultDenom)/resultDenom; //the whole number
		   resultNumer =resultNumer%resultDenom; //remainder
   
					
						szTable[nTableCount] =	'<table id="'+ 'TableStep' + (nTableCount + 1) + '" style="text-align:center;font-size:large" border="0" cellpadding="0" cellspacing="0"> \
							<tr>											 \
								<td rowspan="2">'+ (szTemp=(isNegative==true)?'=&#45;':'=') +'</td> 						 \
								<td rowspan="2">' + nNum + '</td>			 \
								<td>' + resultNumer + '</td>				 \
								<td style="font-size:x-small; padding-left:20px; text-align:left; color:maroon" rowspan="2">&nbsp;</td>					 \
							</tr>											 \
							<tr>											 \
								<td style="border-top: 2px solid rgb(0, 0, 0)">' + resultDenom + '</td>				 \
							</tr>											 \
					    </table>'
						nTableCount++; 
 		}//

 }//else


}

function UpdateDocument()
{

 var szTableName;
 
 for(var nCount = 1; nCount <= nTableCount; nCount++)
 {
  szTableName = 'TableStep' + nCount;
  //alert(szTableName);
  document.getElementById(szTableName).innerHTML = szTable[nCount-1];
 }
  
 for(var nCount = nTableCount; nCount <10; nCount++)
 {
  szTableName = 'TableStep' + (nCount + 1);
  //alert(szTableName);
  document.getElementById(szTableName).innerHTML = '';
 }

  //set the message;
  document.getElementById('alertMessage').innerHTML ='<b style="color:navy">*See the solution and answer below...</b>';

}


function AutoGenerate()
{	

	document.fractionCalc.N1.value = Math.ceil(Math.random()*20);
	document.fractionCalc.N2.value = Math.ceil(Math.random()*20);
	document.fractionCalc.D1.value = Math.ceil(Math.random()*20);
	document.fractionCalc.D2.value = Math.ceil(Math.random()*20);
	/*
	var OpValue = Math.floor(Math.random()*4);;
	switch (OpValue)
	{
		case 0:
		   document.fractionCalc.OP.value = '+';
		  break;
	
  		case 1:
		  document.fractionCalc.OP.value ='-';
		  break;
  		
  		case 2:
  		  document.fractionCalc.OP.value = '*';
		  break;

        case 3:
		  document.fractionCalc.OP.value = '/';
		 break;

    }
    */
     //set the message;
 	 document.getElementById('alertMessage').innerHTML ='';



}


function ClearBoxes()
{
	document.fractionCalc.N1.value = '';
	document.fractionCalc.N2.value = '';
	document.fractionCalc.D1.value = '';
	document.fractionCalc.D2.value = '';

	 //set the message;
 	 document.getElementById('alertMessage').innerHTML ='';

}
















//This code was created by the fine folks at Switch On The Code - http://blog.paranoidferret.com
//This code can be used for any purpose

function animate(elementID, newLeft, newTop, newWidth,
      newHeight, time, callback)
{
  var el = document.getElementById(elementID);
  if(el == null)
    return;
 
  var cLeft = parseInt(el.style.left);
  var cTop = parseInt(el.style.top);
  var cWidth = parseInt(el.style.width);
  var cHeight = parseInt(el.style.height);
 
  var totalFrames = 1;
  if(time> 0)
    totalFrames = time/40;

  var fLeft = newLeft - cLeft;
  if(fLeft != 0)
    fLeft /= totalFrames;
 
  var fTop = newTop - cTop;
  if(fTop != 0)
    fTop /= totalFrames;
 
  var fWidth = newWidth - cWidth;
  if(fWidth != 0)
    fWidth /= totalFrames;
 
  var fHeight = newHeight - cHeight;
  if(fHeight != 0)
    fHeight /= totalFrames;
   
  doFrame(elementID, cLeft, newLeft, fLeft,
      cTop, newTop, fTop, cWidth, newWidth, fWidth,
      cHeight, newHeight, fHeight, callback);
}

function doFrame(eID, cLeft, nLeft, fLeft,
      cTop, nTop, fTop, cWidth, nWidth, fWidth,
      cHeight, nHeight, fHeight, callback)
{
   var el = document.getElementById(eID);
   if(el == null)
     return;

  cLeft = moveSingleVal(cLeft, nLeft, fLeft);
  cTop = moveSingleVal(cTop, nTop, fTop);
  cWidth = moveSingleVal(cWidth, nWidth, fWidth);
  cHeight = moveSingleVal(cHeight, nHeight, fHeight);

  el.style.left = Math.round(cLeft) + 'px';
  el.style.top = Math.round(cTop) + 'px';
  el.style.width = Math.round(cWidth) + 'px';
  el.style.height = Math.round(cHeight) + 'px';
 
  if(cLeft == nLeft && cTop == nTop && cHeight == nHeight
    && cWidth == nWidth)
  {
    if(callback != null)
      callback();
    return;
  }
   
  setTimeout( 'doFrame("'+eID+'",'+cLeft+','+nLeft+','+fLeft+','
    +cTop+','+nTop+','+fTop+','+cWidth+','+nWidth+','+fWidth+','
    +cHeight+','+nHeight+','+fHeight+','+callback+')', 40);
}

function moveSingleVal(currentVal, finalVal, frameAmt)
{
  if(frameAmt == 0 || currentVal == finalVal)
    return finalVal;
 
  currentVal += frameAmt;
  if((frameAmt> 0 && currentVal>= finalVal)
    || (frameAmt <0 && currentVal <= finalVal))
  {
    return finalVal;
  }
  return currentVal;
}

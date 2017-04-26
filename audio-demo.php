<!DOCTYPE html>
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?>
<html>
<head>
  <title>Audio Modem Interface - Technology Demo </title>
  <link rel="icon" href="image/favicon.jpg">
  <!--<script src="js/web-audio-recorder-js-master/lib-minified/WebAudioRecorder.min.js"></script> -->
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/dygraph/1.1.1/dygraph-combined.js"></script>

    <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css";>

  <!-- jQuery library -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

  <!-- Latest compiled JavaScript -->
  <script src= "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

</head>
<body>
  <div id='page-wrapper'>
    <!--NavBar-->
    <?php

    ?>
    <!--End NavBar-->
    <div id="welcome">
      <div id="prelim-demos">
        <div id = "recording-demo" class = "col-xs-6">
          <h3>Recording JS script demo</h3>

          <button type="button" onclick="audRecord(this)">Start/Stop</button>
          <br>
          <br>

          The buffer length: <b id="bufferLengthDisplay"></b><br>
          The sampling rate: <b id="sampleRate"></b><br>
          Recording Status: <b id = "waveform-message"></b>
        </div>
        <div id ="Audio Buffer Demo" class = "col-xs-6">
        <h3>Play Audio Buffer</h3>
          <input type="radio" value="tones" id="bufChoice" name = "bufChoice"> Play the following tones:
          <input type="checkbox" name="tonevals" value="1700" onclick="updateFreqsToPlay()">1700Hz
          <input type="checkbox" name="tonevals" value="1300" onclick="updateFreqsToPlay()">1300Hz<br>
          <input type="radio" value="bits" id="bufChoice" name = "bufChoice"> Transmit String:
          <input type="text" size="8" value="10101101" id="sequence"><br>
          Transmitted bits: <b id="transmittedBits"></b><br><br>
          <button type="button" onclick="audioBufferDemo()">Play Buffer</button>
          <button type="button" onclick="stopBuffer()">Stop Buffer</button>
        </div>
      </div>
      <div id = "waveform-demos">
        <div id = "zoomed-waveform-wrapper">
          <h3>Waveform</h3>
          <div id="chart_div" style="height: 230px; width:auto"></div>
        </div>
      </div>

      <div id ="Frequency-detect" class = "col-xs-6">
        <h3>Frequency Detector</h3>
            1700 Hz Presence: <b id="1700presence"></b> &nbsp;(<b id="1700percent"></b>%) <br>
            1300 Hz Presence: <b id="1300presence"></b>&nbsp;(<b id="1300percent"></b>%)<br>
            Total Power     : <b id="totalPower"></b><br>
            Signal Process State: <b id="signal-Process-State"></b><br>
            Received Bits: <b id="receivedBits"></b><br>
            Error Rate: <b id="error-rate"></b><br>
            Received String: <b id="received-string"></b><br><br>
            Test Presence <b id="test-Presence"></b><br>
            Test total Pwr    : <b id="test-Power"></b><br>
      </div>


      <div id ="crypt-handshakes" class = "col-xs-6">
        <h3>Cryptographic Handshake Demo</h3>
        <div id = "crypt-inputs" class = "col-xs-6">
          Type in your username <br>
          <input type="text" id="username">
          <button type="button" onclick="sendLoginReq()">Submit</button><br>
          Enter name and paste Authentication challenge here<br>
          <input type="text" id="username2">
          <textarea style="height:200px" id="encrypted-message"></textarea>
          <button type="button" onclick="sendDecryptReq()">Submit</button><br>
          Paste supposed hash of original message here<br>
          <textarea style="height:200px" id="decrypted-hash"></textarea>
          <button type="button" onclick="sendAuthReq()">Submit</button><br>
        </div>
        <div id = "crypt-results" class = "col-xs-6" style="word-wrap: break-word;">
          <div id ="login-response-wrapper" class = "well">
            <div>Log in response will show here (An encrypted message)</div><br>
            <div id ="login-response"></div>
          </div>
          <div id = "decrypt-response-wrapper" class = "well">
            <div>Decryption response will show here (Hash of secret message)</div><br>
            <div id ="decrypt-response"></div>
          </div>
          <div id = "auth-result-wrapper" class = "well"><br>
            <div>Login result will show here (successful or unsuccessful)</div>
            <div id ="auth-result"></div>
          </div>
        </div>
      </div>

      <div id ="AJAX call Demo" class = "col-xs-6">
        <h3>AJAX Call Demo</h3>
        Input a number and the server will add one to it <br>
        <input type="text" size="8" id="ajaxNumber">
        <button type="button" onclick="AJAXdemo()">Submit</button>
        <div id = "AJAX-result">
          The result will display here
        </div>
      </div>

      <div id = "waveform-gen" class = "col-xs-6">
        <div id="wave-controls">
          <h3>Select Waveform</h3>
          <div id="type-select" onclick = "changeType()">
            <input type="radio" value="sine" id="wave-type" name = "wave-type"> Sine
            <input type="radio" value="square" id="wave-type" name = "wave-type"> Square
            <input type="radio" value="sawtooth" id="wave-type" name = "wave-type"> Saw
          </div>
          <br>
          <br>
          Frequency: <input type="text" size="4" value="440" id="freq">     
          <br>
          <br>
          <button id="playButton" onclick="playOsc()"> Play tone</button>
          <button id="muteButton" onclick="muteOsc()">Mute</button>
        </div>
        <div id = "wave-status">
        </div>
      </div>
    </div>

    <script>
    
var context = null, sampleRate = null, rec_volume = null, audioInput = null;
var areRecording = false;
var recordingLength = 0;
var bufferSize = 2048;
var leftchannel = [];
var leftBuffer = [];
var AudioContext = window.AudioContext || window.webkitAudioContext;
var audioContext = null;
var oscillator;
var amp;

var wav_message = document.getElementById('waveform-message');

var channels = null;
var bufferLength = null;
var myAudioBuffer = null;

var globalSource = null

function AJAXdemo(){
  var numberToAdd = document.getElementById("ajaxNumber").value
  var requestStr = "numberToAdd=" + numberToAdd
  // var xmlhttp = new XMLHttpRequest();
  // xmlhttp.onreadystatechange = function() {
  //     if (this.readyState == 4 && this.status == 200) {
  //         document.getElementById("AJAX-result").innerHTML = this.responseText;
  //     }
  //   }
  // xmlhttp.open("POST", "projects/ajaxDemo.php", true);
  // xmlhttp.send(requestStr);
  $.ajax({
  type: "POST",
  url: "ajaxDemo.php",
  data: requestStr,
  cache: false,
  success: function(result){
    document.getElementById("AJAX-result").innerHTML = result
  }
});
}

function sendLoginReq(){
  var uname = document.getElementById("username").value
  //var udata = document.getElementById("data").value
  var newdata = { 'username': uname };// , 'data': udata};
  $.ajax({
  type: "POST",
  url: "login1.php",
  data: newdata,
  cache: false,
  success: function(result){
    document.getElementById("login-response").innerHTML = result
    }
  });
}

function sendDecryptReq(){
  var emsg = document.getElementById("encrypted-message").value
  var uname = document.getElementById("username2").value
  var requestStr = 'decryptedHash=' + encodeURIComponent(emsg) + "&username=" + uname
  $.ajax({
  type: "POST",
  url: "decrypt2.php",
  data: requestStr,
  cache: false,
  success: function(result){
    document.getElementById("decrypt-response").innerHTML = result
    }
  });
}

function sendAuthReq(){
  var dhash = document.getElementById("decrypted-hash").value
  var requestStr = "emessage=" + dhash
  $.ajax({
  type: "POST",
  url: "auth3.php",
  data: requestStr,
  cache: false,
  success: function(result){
    document.getElementById("auth-result").innerHTML = result
    }
  });
}



// Create an oscillator and an amplifier.
function initAudio()
{
    // Use audioContext from webaudio_tools.js
    
    console.log("Trying to make oscillator");
    audioContext = new AudioContext();
    testDetectFreq();
    samplesPerBaud = audioContext.sampleRate/baudRate
    oscillator = audioContext.createOscillator();
        //fixOscillator(oscillator);
        oscillator.frequency.value = 440;
        amp = audioContext.createGain();
        amp.gain.value = 0;
        
        // Connect oscillator to amp and amp to the mixer of the audioContext.
        // This is like connecting cables between jacks on a modular synth.
        oscillator.connect(amp);
        amp.connect(audioContext.destination);
        oscillator.start(0);

// Older browsers might not implement mediaDevices at all, so we set an empty object first
if (navigator.mediaDevices === undefined) {
  navigator.mediaDevices = {};
}

  // Some browsers partially implement mediaDevices. We can't just assign an object
  // with getUserMedia as it would overwrite existing properties.
  // Here, we will just add the getUserMedia property if it's missing.
  if (navigator.mediaDevices.getUserMedia === undefined) {
    navigator.mediaDevices.getUserMedia = function(constraints) {

      // First get ahold of the legacy getUserMedia, if present
      var getUserMedia = (navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia ||
        navigator.mozGetUserMedia || navigator.msGetUserMedia);

      // Some browsers just don't implement it - return a rejected promise with an error
      // to keep a consistent interface
      if (!getUserMedia) {
        return Promise.reject(new Error('getUserMedia is not implemented in this browser'));
      }

      // Otherwise, wrap the call to the old navigator.getUserMedia with a Promise
      return new Promise(function(resolve, reject) {
        getUserMedia.call(navigator, constraints, resolve, reject);
      });
    }
  }
  navigator.mediaDevices.getUserMedia({audio: true}).then(gotAudioStream).catch(noAudioerr);

    //INIT for buffer player
    // Stereo
    channels = 2;
}

//---------------Functionality for Audio Buffer Player----------------
var freqsToPlay = [];

freqsToPlay.contains = function(obj) {
    for (var i = 0; i < freqsToPlay.length; i++) {
        if (freqsToPlay[i] === obj) {
            return true;
        }
    }
    return false;
}

function updateFreqsToPlay() {
$(':checkbox').each(function() {  
        var tag = $(this).attr('value');
        //tag.replace(/^quicklinkscb_/, '');      
        if( $(this).is(':checked')){
          if(!freqsToPlay.includes(tag)){
            freqsToPlay.push(tag)
          }
        }
        else{
          var index = freqsToPlay.indexOf(tag);
          if (index > -1) {
            freqsToPlay.splice(index, 1);
          }
        }
    });
  
}

function audioBufferDemo() {
  var choice = $('input[name=bufChoice]:checked').val();
    if(choice == "tones"){
      playTones()
    }
    else
    {
      playString()
    }

}

function playTones() {

  //Number of samples per frame
  bufferLength = audioContext.sampleRate * 3;
  myAudioBuffer = audioContext.createBuffer(2, bufferLength, audioContext.sampleRate)

  for (var channel = 0; channel < channels; channel++) {
   // This gives us the actual ArrayBuffer that contains the data
   var nowBuffering = myAudioBuffer.getChannelData(channel);
   for (var i = 0; i < bufferLength; i++) {
      var sF1700 = audioContext.sampleRate / 1700
      var sF1300 = audioContext.sampleRate / 1300
      var currentSine = null;
      if(freqsToPlay.includes("1700") && freqsToPlay.includes("1300")){
      
        currentSine =  Math.sin(i / (sF1700 / (Math.PI*2))) + Math.sin(i / (sF1300 / (Math.PI*2)))
      }
      else if (freqsToPlay.includes("1700")) {
       currentSine =  Math.sin(i / (sF1700 / (Math.PI*2)))
      }
      else if (freqsToPlay.includes("1300")) {
        currentSine =  Math.sin(i / (sF1300 / (Math.PI*2)))
      }

     nowBuffering[i] = currentSine
   }
  }
  playBuffer()
}

var compbitarray = [];
var compbitarrayIndex = 0;

function playString(){
  var strToPlay = document.getElementById("sequence").value;
  var bits = []
  bits = encodeString(strToPlay)
  playBits([0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1])
  //[0,1,0,1,0,1,0,1,0,1,0,1,0,1,0,1]
}

function encodeString(str){
  var bytes = [];
  var i = 0;
  for (i = 0; i < str.length; ++i) {
    var code = str.charCodeAt(i);
    bytes = bytes.concat([code & 0xff, code / 256 >>> 0]);
  }

  var byteIndex = 0;
  var encBitArr = [];
  var bitIndex = 0;
  for(i = 0; i < bytes.length; i++){
    for(var j = 0; j < 8; j++){
      
      encBitArr[bitIndex] = (bytes[i] >>> (7 - j) & 1);
      bitIndex++
    }
  }

  return encBitArr
}

function playBits(bits) {
  //var strToPlay = document.getElementById("sequence").value;
  // var bytes = [];
  // var bitarr = [];
  var baud = baudRate;
  var sF1700 = audioContext.sampleRate / 1700
  var sF1300 = audioContext.sampleRate / 1300

  // for (var i = 0; i < strToPlay.length; ++i) {
  //   var code = strToPlay.charCodeAt(i);
  //   bytes = bytes.concat([code & 0xff, code / 256 >>> 0]);
  // }

  //Determine how many samples the whole buffer will be
  //We want to include the sync bauds as well, filling 2 buffers on each side with syncing bauds (signal)
  var nSyncBauds = Math.ceil(bufferSize/baudRate) * 6;
  var halfSyncBauds = nSyncBauds/2;

  var nbauds = bits.length + nSyncBauds
  bufferLength = nbauds * samplesPerBaud;
  myAudioBuffer = audioContext.createBuffer(2, bufferLength, audioContext.sampleRate)
  
  
  for (var channel = 0; channel < channels; channel++) {
   // This gives us the actual ArrayBuffer that contains the data
   var nowBuffering = myAudioBuffer.getChannelData(channel);
   var currentSine = null;
   var bitarrIndex = 0
   var syncIndex = 0;
   var currentBit = null
   var i = 0
   var starting = true
   var ending = false
   for (var k = 0; k < nbauds; k++) {

    //Determine if we are syncing yet (either beginning or ending the signal)
    if( starting || ending ){
      currentBit = 2
    }
    else{
      currentBit = bits[bitarrIndex]
      bitarrIndex++
      compbitarray[compbitarrayIndex] = currentBit;
      compbitarrayIndex++
    }
    //bitarr[bitarrIndex] = currentBit
    

    //Fill baud with the kind of signal we want
    for (var j = 0; j < samplesPerBaud; j++ ){
      if (currentBit == 1) {
       currentSine =  Math.sin(i / (sF1700 / (Math.PI*2)))
      }
      else if(currentBit == 0) {
        currentSine =  Math.sin(i / (sF1300 / (Math.PI*2)))
      }
      else{
        currentSine = Math.sin(i / (sF1700 / (Math.PI*2))) + Math.sin(i / (sF1300 / (Math.PI*2)))
      }
       nowBuffering[i] = currentSine
       i++
    }

    //Overhead: Watching for beginning and end and updating the indicies of the bits and bytes
    if (starting){
      syncIndex++
      if(syncIndex == (2/3) * nSyncBauds){
        syncIndex = 0
        starting = false
      }
      continue
    }

    if(!ending){
      //bitIndex++
      ending = (bitarrIndex == bits.length);
      continue;
    }

    
    // if (ending){
    //   syncIndex++
    //   if(syncIndex == halfSyncBauds){
    //     syncIndex = 0
    //   }
    // }

    // if(bitIndex == 8)
    // {
    //   bitIndex = 0
    //   byteIndex++
    // }
   }
 }
  document.getElementById("transmittedBits").innerHTML = bits.join("");
  playBuffer()
}

function playBuffer(){
  // Get an AudioBufferSourceNode.
  // This is the AudioNode to use when we want to play an AudioBuffer
  var source = audioContext.createBufferSource();
  // set the buffer in the AudioBufferSourceNode
  source.buffer = myAudioBuffer;
  // connect the AudioBufferSourceNode to the
  // destination so we can hear the sound
  source.connect(audioContext.destination);

  globalSource = source 
  // start the source playing
  source.start();
}

function stopBuffer(){
  source.stop()
}


//------------Oscillator Demo ----------------

// Set the frequency of the oscillator and start it running.
function startTone(frequency,type)
{
  if(type == undefined)
    type = "sine";

  console.log("Trying to play");
  oscillator.frequency.value = frequency;
  oscillator.type = type;
  amp.gain.value = 0.5;
  
}

function changeType()
{
  var type = $('input[name=wave-type]:checked').val(); 
  oscillator.type = type;
}

function muteOsc()
{
  amp.gain.value = 0.0;
}

function playOsc() 
{

  var freq = document.getElementById("freq").value;
  freq = Number(freq);
  if (isNaN(freq))
  {
    document.getElementById("wave-status").innerHTML = "Frequency entered isn't a Number";
    return;
  }

  //var type = document.getElementById("wave-type").value;
  var type = $('input[name=wave-type]:checked').val();  

  startTone(freq,type);
}


//------------------Functionality for recorder-----------------------------------
class Phasor {
  constructor(freq, mag, phi, power, totPower) {
    this.freq = freq;
    this.mag = mag;
    this.phi = phi;
    this.power = power
    this.totPower = totPower
    this.percent = (power/totPower) * 100
    this.sampleFrequency = audioContext.sampleRate/freq
  }
}


noAudioerr = function(e) {
  console.log("Audio source is not connected or is blocked");
};

gotAudioStream = function(e) {
  console.log("Audio source is connected!");


      // retrieve the current sample rate to be used for WAV packaging
      sampleRate = audioContext.sampleRate;
      document.getElementById('sampleRate').innerHTML = sampleRate;

      // creates a gain node
      rec_volume = audioContext.createGain();

      // creates an audio node from the microphone incoming stream
      audioInput = audioContext.createMediaStreamSource(e);

      // connect the stream to the gain node
      audioInput.connect(rec_volume);
      recorder = audioContext.createScriptProcessor(bufferSize, 1, 1);
      //recorder = new Recorder(rec_volume);

      recorder.onaudioprocess = function(e){
        if(!areRecording) return;
          //console.log ('recording');
          var left = e.inputBuffer.getChannelData (0);
         //process# buffers
          buff = new Float32Array (left)
          processBuffer(buff)
          leftchannel.push (buff);
          recordingLength += bufferSize;
          document.getElementById("bufferLengthDisplay").innerHTML = recordingLength;
          console.log('recording')
        };

        // we connect the recorder
        rec_volume.connect (recorder);
        recorder.connect (audioContext.destination); 
      }



function mergeBuffers(channelBuffer, recordingLength){
    var result = new Float32Array(recordingLength);
    var offset = 0;
    var lng = channelBuffer.length;
    for (var i = 0; i < lng; i++){
      var buffer = channelBuffer[i];
      result.set(buffer, offset);
      offset += buffer.length;
    }
    return result;
  }

var global_dygraph = null;

function drawChart() {

  // Create the data table.
  var time = [];
  var chart_data = [[]];
  var i = 0;
  for(i = 0; i < leftBuffer.length;i++)
  {
   time[i] = i///sampleRate;
   chart_data[i] = [time[i],leftBuffer[i]]; 
 }

 var my_dygraph = new Dygraph(document.getElementById("chart_div"),
  chart_data,
  {
    title: 'Captured Waveform',
    ylabel: 'Amplitude',
    xlabel: 'time (s)',
    labels: [ "Time", "Amplitude" ],

    underlayCallback: function(canvas, area, g) {
      for(var i = 0; i < observePoints.length; i++){
        highlightArea(canvas, area, g, observePoints[i][0], observePoints[i][1]);
      }
    }
  });

 global_dygraph = my_dygraph
}

function addAnnotations(g){
  var annotations = []

  for(var i = 0; i < observePoints.length; i++){
    annotations[i] = 
    {
      series: "Amplitude",
      x: observePoints[i][0],
      shortText: i.toString() + ":" + rx_bits[i].toString(),
      width: 32
    }
  }
  g.setAnnotations(annotations);
}

function highlightArea(canvas, area, g, l, r){
  var bottom_left = g.toDomCoords(l, -20);
  var top_right = g.toDomCoords(r, +20);

  var left = bottom_left[0];
  var right = top_right[0];

  canvas.fillStyle = "rgba(255, 255, 102, 1.0)";
  canvas.fillRect(left, area.y, right - left, area.h);
}

function recordDemo() {
  areRecording = true;
  // we flat the left and right channels down
  leftBuffer = mergeBuffers ( leftchannel, recordingLength );
  wav_message.innerHTML = 'Recording...';
}

function stopRecording() {
  areRecording = false;
  wav_message.innerHTML = 'Done Recording...';
  //window.alert("Your recording has a buffer size of:" + recordingLength);
}

function recordClear() {
  recordingLength = 0;
  leftBuffer = [];
  leftchannel = [];
}


function createTable(tableData) {
  var oldTable = document.getElementById('percents-table')
  if(oldTable)
    oldTable.parentNode.removeChild(oldTable)
  var table = document.createElement('table'); 
  table.setAttribute("id", "percents-table")
  var tableBody = document.createElement('tbody');

  tableData.forEach(function(rowData) {
    var row = document.createElement('tr');

    rowData.forEach(function(cellData) {
      var cell = document.createElement('td');
      cell.appendChild(document.createTextNode(cellData));
      cell.setAttribute("style","padding-left:22px")
      row.appendChild(cell);
    });

    tableBody.appendChild(row);
  });

  table.appendChild(tableBody);
  document.body.appendChild(table);
}

function generatePercentTable(){
  var tableData = [[]]
  for(var i = 0; i < pow1700.length; i++){
    tableData[i] = [i, pow1700[i], pow1300[i], rx_bits[i]]
  }
  createTable(tableData)
  addAnnotations(global_dygraph);
}

  function audRecord( e ) {
    if (e.classList.contains("areRecording")){
      stopRecording();
      e.classList.remove("areRecording")
      leftBuffer = mergeBuffers ( leftchannel, recordingLength );
      drawChart();
      generatePercentTable();
    }else{
      e.classList.add("areRecording")
      recordClear();
      recordDemo();
      shift = 0
      bridgeBitSignal = []
      rx_bits = []
      rx_bitsIndex = 0
      observePoints = [[]]
      OPindex = 0;
      globalIndex = 0;
      gotData = false //stays false until we decode a real data bit
      sigProcessState = "idle"
      pow1700 = [];
      pow1300 = [];
      powArrIndex = 0;
    }
  }

//-----------Decoder-------------------
var baudRate = 100
var samplesPerBaud = null
var shift = 0
var bridgeBitSignal = []
var rx_bits = []
var rx_bitsIndex = 0
var gotData = false //stays false until we decode a real data bit
var sigProcessState = "idle"

var globalIndex = 0;
var observePoints = [[]];
var OPindex = 0;

var lastBuffer = [];

function processBuffer(buffer){
  var phasor1700 = detectFreq(buffer, baudRate, 1700)
  var phasor1300 = detectFreq(buffer, baudRate, 1300)

  //document.getElementById("1700presence").innerHTML = phasor1700.mag.toFixed(6);
  //document.getElementById("1300presence").innerHTML = phasor1300.mag.toFixed(6);
  document.getElementById("1700percent").innerHTML = phasor1700.percent.toFixed();
  document.getElementById("1300percent").innerHTML = phasor1300.percent.toFixed();
  document.getElementById("totalPower").innerHTML = phasor1300.totPower;
  document.getElementById("signal-Process-State").innerHTML = sigProcessState;
  

  switch (sigProcessState)
  {
    case "idle":
      //wait for a sync signal to be present. Otherwise do nothing
      var transmissionPresent = detectSync(buffer, phasor1700, phasor1300)
      if (transmissionPresent)
        sigProcessState = "transmissionDetected"
      
      globalIndex += bufferSize;
      break;

    case "transmissionDetected":
      //"Do nothing" We are just gathing the sync signal to get a good sync
      sigProcessState = "syncing";
      globalIndex += bufferSize;
      break;

    case "syncing" :
      //We got two buffers. Find were a chip starts with these buffers
      var bigBuf = new Float32Array(bufferSize *2 )
      bigBuf.set(lastBuffer)
      bigBuf.set(buffer, bufferSize)
      phasor1700 = detectFreq(bigBuf, baudRate, 1700)
      phasor1300 = detectFreq(bigBuf, baudRate, 1300) 
      var first_shift = findChip2(phasor1300, phasor1700)

      //Also start decoding from here on out
      shift = decodeBuffer(buffer, first_shift, false)
      sigProcessState = "recieving"
      globalIndex += bufferSize;
      break

    case "recieving" :
      shift = decodeBuffer(buffer, shift, true)
      globalIndex += bufferSize;
      if(gotData && rx_bits[rx_bitsIndex -1] == 2 && rx_bits[rx_bitsIndex - 2] == 2)
        sigProcessState = "endingTransmission"
      break
    case "endingTransmission" :
      console.log("Ending transsmssion")
      processBits(rx_bits)
      sigProcessState = "awaitNothing"
      break;
    case "awaitNothing":
      break;

  }
  lastBuffer = [];
  lastBuffer = buffer;
}

function testAccuracy(arr){
  var errCt = 0;
  for(var i = 0; i < compbitarray.length; i++){
    if(arr[i] != compbitarray[i]){
      errCt++
    }
  }
  var errRt = errCt/compbitarray.length
  document.getElementById("error-rate").innerHTML = errRt;
}

function processBits(bits_arr){
  //Strip the 2's from the bit array
  //We are assuming that all 2's (End/Start bits) are only at the end
  var new_bits = []
  var j = 0
  var i = 0
  var u8ArrIndex = 0;
  var uint8 = new Uint8Array();

  //Strip the 2's
  for(i = 0; i < bits_arr.length; i++){
    if(bits_arr[i] != 2){
      new_bits[j] = bits_arr[i]
      j++
    }
  }
  


  document.getElementById("receivedBits").innerHTML = new_bits;
  testAccuracy(new_bits);
  //All data is transferred in bytes. If the number of bits are not divisible by 8 then there must be data missing
  if((new_bits.length % 8) != 0){
    console.log("Warning: Not all bits recieved. TODO: make error handler for this")
    //return ""
  }

  //first get a string of each of the bits into a "byte string"
  while(u8ArrIndex * 8 < new_bits.length){
    var byte = "";
    for(i=0;i<8;i++){
      byte += new_bits[i].toString()
    }

    //Convert that byte into an integer
    var intByte = parseInt(byte, 2);
    //Make that integer 8 bit
    uint8[u8ArrIndex] = intByte;
    u8ArrIndex++
  }

  var result = "";
  for(var i = 0; i < uint8.length; ++i){
    result+= (String.fromCharCode(uint8[i]));
  }

  document.getElementById("received-string").innerHTML = result
}

var syncAvg1700 = 0;
var syncAvg1300 = 0;

function decodeBuffer(buffer, shift, inTransmission){

  var sigWindow = []
  var fullBridgeBitSignal = []
  var sigWindowSize = (samplesPerBaud)/2
  var sigWindowShift = sigWindowSize/2
  var i = 0
  var j = 0
  var bufferIndex = shift
  //if this isn't the very first buffer to detect a transmission
  if(inTransmission){
    //Record for debug where samples are being analyzed
    observePoints[OPindex] = [globalIndex + shift - samplesPerBaud + sigWindowShift, globalIndex + shift - samplesPerBaud + sigWindowShift + sigWindowSize];
    OPindex++
    //copy over the samples from the last buffer
    for(i = 0; i < bridgeBitSignal.length; i++){
      fullBridgeBitSignal[i] = bridgeBitSignal[i]
    }
    //now copy the other half that takes up the beginning of this buffer
    j = i;
    for(i = 0; i < shift; i++){
      fullBridgeBitSignal[j] = buffer[i]
      j++
    } 
    //Now make a small buffer that is in the middle of this bit signal
    //which is half the size of a signal and is placed in the middle of the bit signal
    j = sigWindowShift
    for(i = 0; i < sigWindowSize; i++){
      sigWindow[i] = fullBridgeBitSignal[j]
      j++
    }
    //detect which bit is in the signal and record it to rx_bits array
    rx_bits[rx_bitsIndex] = detectBit(sigWindow)
    rx_bitsIndex++
  }

  var numChips = 0;
  var manSync = false
  //Now decode all the bits after shift
  //Do not decode if a full bit signal (baud) cannot fit
  while ((bufferIndex + samplesPerBaud) < buffer.length){
    numChips++;
    //make a small buffer sigWindow that's in the middle of a bit signal (same as before)
    j = bufferIndex + sigWindowShift
    observePoints[OPindex] = [globalIndex+j,globalIndex+j+sigWindowSize];
    OPindex++;
    for(i = 0; i < sigWindowSize; i++){
      sigWindow[i] = buffer[j]
      j++
    }

    //decode the bit -- same as before
    rx_bits[rx_bitsIndex] = detectBit(sigWindow)
    rx_bitsIndex++


    if(!inTransmission){
      syncAvg1300 += pow1300[powArrIndex];
      syncAvg1700 += pow1700[powArrIndex];
    }

    // Do fine syncing if we are still there. Otherwise just go onto the the next chip
    if(manSync){
      var diff1700 = (pow1700> syncAvg1700+5) || (pow1700> syncAvg1700-5)
      var diff1700 = (pow1300> syncAvg1300+5) || (pow1300> syncAvg1300-5)
      if(diff1700 || diff1700);
    }
    bufferIndex += samplesPerBaud
  }

  if(!inTransmission){
    syncAvg1700 = syncAvg1700/(numChips);
    syncAvg1300 = syncAvg1300/(numChips);
    manSync = true;
  }

  //Prepare the remaining samples for the bridgeBitSignal - copy it there
  bridgeBitSignal = []
  i = 0
  for(j = bufferIndex; j < buffer.length; j++){
    bridgeBitSignal[i] = buffer[j]
    i++
  }
  //return where the next full signal will start. This will be the next singal's shift
  return (bufferIndex + samplesPerBaud) % bufferSize
}

var pow1700 = [];
var pow1300 = [];
var powArrIndex = 0;


function detectBit(buffer){
  phasor1700 = detectFreq(buffer, baudRate, 1700)
  phasor1300 = detectFreq(buffer, baudRate, 1300)

  pow1700[powArrIndex] = phasor1700.percent;
  pow1300[powArrIndex] = phasor1300.percent;
  powArrIndex++;

  if(Math.abs(phasor1700.percent - phasor1300.percent) <= 40)
    return 2;

  if(phasor1700.percent > phasor1300.percent){
    //gotData = true;
    return 1;
  }
  else{
    //gotData = true;
    return 0;
  }

  // if((phasor1300.percent > 3.0) && (Math.floor(phasor1700.percent) == 0)){
  //   gotData = true;
  //   return 0;
  // }
  // else if((phasor1700.percent > 3.0) && (Math.floor(phasor1300.percent) == 0)){
  //   gotData = true;
  //   return 1;
  // }
  // else{
  //   //this is a sync bit so just return 2 which will be discarded
  //   return 2;
  // }

}

function testDetectFreq2(){
  //implementation of the goertzel alg
  var buffer = [];
  var i =0;
  var sampleFrequency = audioContext.sampleRate/1700;

  while(i < 2048 - 1){
    buffer[i] = 0.0001 * Math.sin(i / (sampleFrequency / (Math.PI*2))) 
    i++;
  }

  var k = 0.5 + (2048*1700/audioContext.sampleRate)
  var w = (2*Math.PI/2048) * k;
  var cosine = Math.cos(w);
  var sine = Math.sin(w);
  var coeff = 2 * cosine;
  var Q0 = 0, Q1 = 0, Q2 = 0;

  var totPower = 0;

  i = 0;
  while(i < 2048 - 1){
    Q0 = coeff * Q1 - Q2 + buffer[i];
    Q2 = Q1;
    Q1 = Q0;
    totPower += buffer[i] * buffer[i]
    i++
  }

  var real = (Q1 - Q2 * cosine)
  var imag = (Q2 * sine)
  var mag = Math.sqrt(real * real + imag * imag);

  document.getElementById("test-Presence").innerHTML = mag;
  document.getElementById("test-Power").innerHTML = totPower;
}

function testDetectFreq(){
  var buffer = [];
  var i =0;
  var sampleFrequency = audioContext.sampleRate/1700;
  var sampleFrequency2 = audioContext.sampleRate/1300;
  var maxAmp = 0;
  while(i < 2048 - 1){
    buffer[i] = 0.01 * Math.sin(i / (sampleFrequency / (Math.PI*2))) + 0.01 * Math.sin(i / (sampleFrequency2 / (Math.PI*2)))
    i++;
  }

  i = 0;
  var Sine = 0;
  var Cosine = 0;
  var totPower = 0;

  // while (i < 2048 - 1){
  //   totPower += buffer[i] * buffer[i]
  //   if(Math.abs(buffer[i]) > maxAmp){
  //     maxAmp = Math.abs(buffer[i]);
  //   }
  //   i++
  // }


  i = 0;
  //var sinAmp = Math.sqrt(totPower);
  while (i < 2048 - 1){
    totPower += buffer[i] * buffer[i]
    Sine += buffer[i] *  Math.sqrt(2) * Math.cos(i / (sampleFrequency / (Math.PI*2)))
    Cosine += buffer[i] * Math.sqrt(2) *Math.sin(i / (sampleFrequency / (Math.PI*2)))
    i++
  }

  totPower = totPower/2048
  Sine = Sine/2048
  Cosine = Cosine/2048
  var power = Sine * Sine + Cosine * Cosine;
  document.getElementById("test-Power").innerHTML = totPower;
  var mag = Math.sqrt(power)
  document.getElementById("test-Presence").innerHTML = power;

}

//  var sine_dy = new Dygraph(document.getElementById("sine-viz"),
// sinew,
// {
//   title: 'Generated Sine',
//   ylabel: 'Amplitude',
//   xlabel: 'time (s)',
//   labels: [ "Time", "Amplitude" ]
// });

//   var buffer_dy = new Dygraph(document.getElementById("buffer-viz"),
// buffer_graphdata,
// {
//   title: 'Caputured Buffer',
//   ylabel: 'Amplitude',
//   xlabel: 'time (s)',
//   labels: [ "Time", "Amplitude" ]
// });


function detectFreq(buffer, baud, freq){
  var sample = []
  var bufIndex = 0
  //var normalizedfreq = freq / audioContext.sampleRate;
  var sampleFrequency = audioContext.sampleRate/ freq
  var Sine = 0
  var Cosine = 0
  var power = 0
  var totPower = 0;
  var i = 0;

  // var sinew = [[0,0]]
  // var buffer_graphdata = [[0,0]]
  // var time = []

  while (i < buffer.length - 1){


    // time[i] = i/audioContext.sampleRate;
    // sinew[i] = [time[i],Math.sin(i / (sampleFrequency / (Math.PI*2)))]
    // buffer_graphdata[i] = [time[i],buffer[i]]

    totPower += buffer[i] * buffer[i]
    Sine += buffer[i] *  Math.sqrt(2) * Math.cos(i / (sampleFrequency / (Math.PI*2)))
    Cosine += buffer[i] * Math.sqrt(2) *Math.sin(i / (sampleFrequency / (Math.PI*2)))
    i++
  }

  totPower = totPower/buffer.length
  Sine = Sine/buffer.length
  Cosine = Cosine/buffer.length

  // buffer_dy.updateOptions( { 'file': buffer_graphdata } );
  // sine_dy.updateOptions( { 'file': sinew } );

  //get angle in Radians -PI to PI
  var angle = Math.atan2(Cosine,Sine)

  // if (angle < 0) {
  //   angle += 2 * Math.PI
  // }

  //Map Radians to 0 to samples/period
  var phi = (angle * sampleFrequency)/(2 * Math.PI)

  var mag = Math.sqrt(Sine * Sine + Cosine * Cosine)
  power = Sine * Sine + Cosine * Cosine
  //totPower = totPower/(buffer.length - 1)
  return new Phasor(freq, mag,phi,power, totPower)
}

function findChip2(phasor1, phasor2){
  //posL is intended to be the position for the 1300 chip
  var posL = phasor1.phi
  var posS = phasor2.phi
  //var chipStart =  posS + 52 * (posL-posS)
  //var chipStart = posS + 51 * (posL-posS)
  var chipStart = parseInt(posL + 51 * (posL-posS))
  chipStart = chipStart % samplesPerBaud

  //Make the first shift be positive (Needed so buffer indices are positive)
  while(chipStart < 0){
    chipStart = chipStart + samplesPerBaud
  }
  return chipStart
}

function findChip(phasor1, phasor2)
{
  var shift_p1 = phasor1.phi
  var shift_p2 = phasor2.phi
  var shift_p1_int = parseInt(shift_p1)
  var shift_p2_int = parseInt(shift_p2)

  while (shift_p2_int != shift_p1_int)
  {
    if(shift_p2_int == shift_p1_int + 1)
      break;
    if(shift_p2_int == shift_p1_int - 1)
      break;

    if(shift_p1 < shift_p2){
      shift_p1 += phasor1.sampleFrequency
      shift_p1_int = parseInt(shift_p1)
    }
    else{
      shift_p2 += phasor2.sampleFrequency
      shift_p2_int = parseInt(shift_p2)
    }
  }

  while(shift_p2_int - samplesPerBaud > 0){
    shift_p2_int -= samplesPerBaud
  }

  return shift_p2_int
}
  
function detectSync(buffer, phasor1700, phasor1300)
{
  //Detect the presence of our carrier frequencies
  var present1700 = (phasor1700.percent > 1.0);
  var present1300 = (phasor1700.percent > 1.0);

  return (present1700 && present1300)

}

// init once the page has finished loading.
window.onload = initAudio;

</script>



<?php

?>
</body>
</html>
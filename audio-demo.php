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
      <div id = "waveform-demos">
        <div id = "zoomed-waveform-wrapper">
          <h3>Waveform</h3>
          <div id="chart_div" style="height: 230px; width: 1000px"></div>
        </div>
      </div>

      <div id ="Audio Buffer Demo" class = "col-xs-6">
      <h3>Play Audio Buffer</h3>
        <input type="radio" value="tones" id="bufChoice" name = "bufChoice"> Play the following tones:
        <input type="checkbox" name="tonevals" value="1700" onclick="updateFreqsToPlay()">1700Hz
        <input type="checkbox" name="tonevals" value="1300" onclick="updateFreqsToPlay()">1300Hz<br>
        <input type="radio" value="bits" id="bufChoice" name = "bufChoice"> Transmit String:
        <input type="text" size="8" value="10101101" id="sequence"><br><br>
        <button type="button" onclick="audioBufferDemo()">Play Buffer</button>
        <button type="button" onclick="stopBuffer()">Stop Buffer</button>
      </div>
    </div>

    <div id ="Frequency-detect" class = "col-xs-6">
      <h3>Frequency Detector</h3>
          1700 Hz Presence: <b id="1700presence"></b><br>
          1300 Hz Presence: <b id="1300presence"></b><br>
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

      var currentBroadcast = 0
      var globalSource = null

// Create an oscillator and an amplifier.
function initAudio()
{
    // Use audioContext from webaudio_tools.js
    
    console.log("Trying to make oscillator");
    audioContext = new AudioContext();
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
      playBits()
    }

}

function playTones() {

  //Number of samples per frame
  bufferLength = audioContext.sampleRate * 4;
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

function playBits() {
  var strToPlay = document.getElementById("sequence").value;
  var bytes = [];
  var bitarr = [];
  var baud = 100;
  var sF1700 = audioContext.sampleRate / 1700
  var sF1300 = audioContext.sampleRate / 1300

  for (var i = 0; i < strToPlay.length; ++i) {
    var code = strToPlay.charCodeAt(i);
    bytes = bytes.concat([code & 0xff, code / 256 >>> 0]);
}
  var nbauds = bytes.length * 8 + 2
  bufferLength = nbauds * samplesPerBaud;
  myAudioBuffer = audioContext.createBuffer(2, bufferLength, audioContext.sampleRate)
  
  
  for (var channel = 0; channel < channels; channel++) {
   // This gives us the actual ArrayBuffer that contains the data
   var nowBuffering = myAudioBuffer.getChannelData(channel);
   var currentSine = null;
   var byteIndex = 0
   var bitIndex = 0
   var bitarrIndex = 0
   var currentBit = null
   var i = 0
   var starting = true
   var ending = false
   for (var k = 0; k < nbauds; k++) {

    //If this is the very first bit or very last bit, send start/stop bit
    if( starting || ending ){
      currentBit = 2
    }
    else{
      currentBit = (bytes[byteIndex] >>> (7 - bitIndex) & 1)
    }
    bitarr[bitarrIndex] = currentBit
    currentBroadcast = currentBit
    bitarrIndex++

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
      starting = false
      continue
    }

    bitIndex++
    ending = ((byteIndex == bytes.length -1) && bitIndex == 8)
    if (ending)
      continue

    if(bitIndex == 8)
    {
      bitIndex = 0
      byteIndex++
    }
   }
  }
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
  constructor(freq, mag, phi) {
    this.freq = freq;
    this.mag = mag;
    this.phi = phi;
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


function drawChart() {

  // Create the data table.
  var time = [];
  var chart_data = [[]];
  var i = 0;
  for(i = 0; i < leftBuffer.length;i++)
  {
   time[i] = i/sampleRate;
   chart_data[i] = [time[i],leftBuffer[i]]; 
 }

 var my_dygraph = new Dygraph(document.getElementById("chart_div"),
  chart_data,
  {
    title: 'Caputured Waveform',
    ylabel: 'Amplitude',
    xlabel: 'time (s)',
    labels: [ "Time", "Amplitude" ]
  });
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

  function audRecord( e ) {
    if (e.classList.contains("areRecording")){
      stopRecording();
      e.classList.remove("areRecording")
      leftBuffer = mergeBuffers ( leftchannel, recordingLength );
      drawChart();
    }else{
      e.classList.add("areRecording")
      recordClear();
      recordDemo();
    }
  }

//-----------Decoder-------------------
var baudRate = 100
var samplesPerBaud = null
var shift = 0
var bridgeBitSignal = []
var rx_bits = []
var rx_bitsIndex = 0
var sigProcessState = "idle"

function processBuffer(buffer){
  var phasor1700 = detectFreq(buffer, 100, 1700)
  var phasor1300 = detectFreq(buffer, 100, 1300)

  document.getElementById("1700presence").innerHTML = phasor1700.mag;
  document.getElementById("1300presence").innerHTML = phasor1300.mag;


  switch (sigProcessState)
  {
    case "idle":
      sigProcessState = detectTransmission(buffer, phasor1700, phasor1300)
      if (sigProcessState == "idle")
        break

    case "transmissionDetected" :
      //Find where a chip begins in this buffer
      var first_shift = findChip(phasor1300, phasor1700)
      
      // //prepare to be decoded - copy the birdgeBitSignal,
      // var outside_shift = first_shift
      // while(outside_shift < bufferSize){
      //   outside_shift += samplesPerBaud
      // }

      // outside_shift = outside_shift % bufferSize
      // var inside_shift = bufferSize - (samplesPerBaud - outside_shift)

      // var j = 0;
      // for(var i = inside_shift; i < bufferSize; i++){
      //   bridgeBitSignal[j] = buffer[i]
      //   j++
      // }

      shift = decodeBits(buffer, first_shift, false)
      sigProcessState = "recieving"
      break

    case "recieving" :
      shift = decodeBits(buffer, shift, true)
      if(rx_bits[rx_bitsIndex -1] == 2 && rx_bits[rx_bitsIndex - 2] == 2)
        sigProcessState = "endingTransmission"
      break
    case "endingTransmission" :
      console.log("Ending transsmssion")
      processBits(rx_bits)
      sigProcessState = "idle"

  }
}

function processBits(bits_arr){
  //Strip the 2's from the bit array
  //We are assuming that all 2's (End/Start bits) are only at the end
  var new_bits = []
  var j = 0
  for(var i = 0; i < bits_arr.length; i++){
    if(bits_arr[i] != 2){
      new_bits[j] = bits_arr[i]
      j++
    }
  }
}

function decodeBits(buffer, shift, inTransmission){
  var sigWindow = []
  var fullBridgeBitSignal = []
  var sigWindowSize = (samplesPerBaud)/2
  var sigWindowShift = sigWindowSize/2
  var i = 0
  var j = 0
  var bIndex = shift
  //if this isn't the very first buffer to detect a transmission
  if(inTransmission){
    //copy over the samples from the last buffer
    for(i = 0; i < bridgeBitSignal.length; i++){
      fullBridgeBitSignal[i] = bridgeBitSignal[i]
    }
    //now copy the other half that takes up the beginning of this buffer
    for(j = 0; j < shift; j++){
      fullBridgeBitSignal[j] = buffer[i]
      i++
    }
    //Now make a small buffer that is in the middle of this bit signal
    //which is half the size of a signal and is placed in the middle of the bit signal
    j = sigWindowShift
    for(i = 0; i < sigWindowSize; i++){
      sigWindow[i] = fullBridgeBitSignal[j]
      j++
    }
    //detect which bit is in the signal and record it to rx_bits array
    rx_bits[rx_bitsIndex] = detectBit(sigWindow, false)
    rx_bitsIndex++
  }
  //Now decode all the bits after shift
  //Do not decode if a full bit signal cannot fit
  while (bIndex + (samplesPerBaud) < buffer.length){
    //make a small buffer sigWindow tat's in the middle of a bit signal (same as before)
    j = bIndex + sigWindowShift
    for(i = 0; i < sigWindowSize; i++){
      sigWindow[i] = buffer[j]
      j++
    }
    //decode the bit -- same as before
    rx_bits[rx_bitsIndex] = detectBit(sigWindow, false)
    rx_bitsIndex++
    // go on to the the next bit signal
    bIndex += samplesPerBaud
  }

  //Prepare the remaining samples for the bridgeBitSignal - copy it there
  bridgeBitSignal = []
  i = 0
  for(j = bIndex; j < buffer.length; j++){
    bridgeBitSignal[i] = buffer[j]
  }
  //return where the next full signal will start. This will be the next singal's shift
  return (bIndex + samplesPerBaud) % bufferSize
}

function detectBit(buffer, strict){
  phasor1700 = detectFreq(buffer, baudRate, 1700)
  phasor1300 = detectFreq(buffer, baudRate, 1300)

  if(phasor1700.mag > 2.9 && phasor1300.mag > 2.9 && !strict){
    //this is a sync bit so just return 2 which will be discarded
    return 2
  }
  else if(phasor1700.mag > phasor1300.mag){
    return 1
  }
  else{
    return 0
  }
}

function detectFreq(buffer, baud, freq){
  var sample = []
  var bufIndex = 0
  //var normalizedfreq = freq / audioContext.sampleRate;
  var sampleFrequency = audioContext.sampleRate/ freq
  var Sine = 0
  var Cosine = 0
  var power = 0
  var i = 0;

  // var sinew = [[0,0]]
  // var buffer_graphdata = [[0,0]]
  // var time = []


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

  while (i < buffer.length - 1){


    // time[i] = i/audioContext.sampleRate;
    // sinew[i] = [time[i],Math.sin(i / (sampleFrequency / (Math.PI*2)))]
    // buffer_graphdata[i] = [time[i],buffer[i]]

    Sine += buffer[i] * Math.cos(i / (sampleFrequency / (Math.PI*2)))
    Cosine += buffer[i] * Math.sin(i / (sampleFrequency / (Math.PI*2)))
    i++

    // power = Math.pow(Sine,2) + Math.pow(Cosine,2)

    // if(power > 10)
    //   break
  }

  // buffer_dy.updateOptions( { 'file': buffer_graphdata } );
  // sine_dy.updateOptions( { 'file': sinew } );

  var angle = Math.atan2(Cosine,Sine)
  if (angle < 0) {
    angle += 2 * Math.PI
  }
  //Map 0 to 2PI to 0 to samples/period
  var phi = (angle * sampleFrequency)/(2 * Math.PI)
  var mag = Math.sqrt(Sine * Sine + Cosine * Cosine)
  return new Phasor(freq, mag,phi)
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
      return shift_p2_int
    if(shift_p2_int == shift_p1_int - 1)
      return shift_p2_int

    if(shift_p1 < shift_p2){
      shift_p1 += phasor1.sampleFrequency
      shift_p1_int = parseInt(shift_p1)
    }
    else{
      shift_p2 += phasor2.sampleFrequency
      shift_p2_int = parseInt(shift_p2)
    }
  }
  return shift_p2_int
}

function detectTransmission()
{
  //TODO. Implement this
}

// init once the page has finished loading.
window.onload = initAudio;

</script>



</body>
</html>
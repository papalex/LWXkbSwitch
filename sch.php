<?php
?>
<svg>
  <defs>		
			<g id="voltdcico">
				<rect width="50" height="50" rx="3" ry="3" style="fill:none; stroke:black;"></rect>
				<path stroke="#555" stroke-width="2" d="M6 44v-25a3,3 0 1 0 1 0 M43 44v-25a3,3 0 1 0 1 0"></path>		
				<path d="M18 20H41m0 -12v1m-35 0h1m-1 0v1" style="stroke-dasharray: 1, 9;stroke:#444; stroke-width: 10;"></path>
				<path d="M13 20H41" style="stroke-dasharray: 1, 9;stroke:#444; stroke-width: 20;"></path>
			</g>
			<g id="swico">	
				<rect width="50" height="50" rx="3" ry="3" style="fill:none; stroke:black;"></rect>		
				<path fill="#eee" d="M10 18h25l9 18h-24l-10 -18"></path>
				<path fill="#aaa" d="M10 18l10 18v6l-10 -16v-9"></path>
				<path fill="#ccc" d="M21 36h24v6h-24v-6"></path>
				<path fill="#444" d="M20 21h9l7 12h-9l-8 -12"></path>
				<path d="M23 12 v13a5,5 0 0 0 6 0v-13h-6" style="fill:white;stroke:black;"></path>
				<path d="M23 17 v-1 a6,6 0 1 1 6 0 v1h-6" style="fill:#b01010;stroke:black;"></path>
				<path d="M27 8 l2 -1 a5,5 0 0 1 1 5 A5,5 0 0 0 27 8" style="fill:white;"></path>
			</g>			
      <g id="resistor">
        <rect  y=-5 x=25 width="35" height="10" rx="1" ry="1" style="fill:none;"stroke="#000"stroke-width="2"></rect>
          <path d="m0 0 h25" stroke="#000" stroke-width="2"></path>
          <path d="m60 0 h25" stroke="#000" stroke-width="2"></path>
        </g>    
       <path id="diode" stroke="#000" stroke-width="2" y=-6 x=25 style="fill:none; stroke:black;" 
              d="m0 0h25 m0 -8 v8 l15 8v-8h25 m-25 0v-8l-15 8 v8"></path>
	  
    <g id="bridge">
      <g transform="translate(60,0) rotate(135) scale(.5)">        
      <use transform="rotate(90)" xlink:href="#diode"></use>
      <use  xlink:href="#diode"></use>
      <use transform="translate(65 0) rotate(90)" xlink:href="#diode"></use>
      <use transform="translate(0 65)" xlink:href="#diode"></use>
        <g transform="rotate(-135)">
      <text y=-10 x=-120> &mdash; </text>
      <text y=-10 x=10> + </text>
        </g>
      </g>      
      <path d="m60 0 h15" stroke="#000" stroke-width="2"></path>
      <path d="m15 0 h-10 v40" fill="none" stroke="#000" stroke-width="2"></path>
      <path d="m37 23 h-45"  stroke="#000" stroke-width="2"></path>
      <path d="m37 -23 h-45" stroke="#000" stroke-width="2"></path>      
    </g>
    <g id="cond">
      <path d="m25 0 h10" style="stroke-dasharray: 2, 6;stroke:#444; stroke-width: 20;"></path>
        <path d="m0 0 h60" stroke="#000" stroke-width="2" style="stroke-dasharray: 25, 10;"></path>
    </g>
		</defs>
</svg>
      <svg><g >
		<use transform="translate(100) rotate(90)" xlink:href="#voltdcico"></use>
        <g transform="translate(150,7)" id="resist_r1"><text x="15" y="30" >R1</text>
        <rect  width="50" height="15" rx="1" ry="1" style="fill:none; stroke:black;"></rect></g>
          <path d="m100 15 h50" fill="#3eb064" stroke="#000" stroke-width="2"></path>
          <path d="m200 15 h25" fill="#3eb064" stroke="#000" stroke-width="2"></path>
		</g></svg>
</svg>
      <svg  width="300" height="200" style="border: solid"><g >
		<use transform="translate(50,20) rotate(90)" xlink:href="#voltdcico"></use>    
    <use transform="translate(60,45) rotate(0)" xlink:href="#bridge"></use>
    <use transform="translate(135,45) rotate(0)" xlink:href="#resistor"></use>
    <use transform="translate(135,45) rotate(90)" xlink:href="#cond"></use>
        
    <use transform="translate(250,50) rotate(90)" xlink:href="#diode"></use>
    <use transform="translate(250,30) rotate(0)" xlink:href="#cond"></use>
        <!--<text x="20" y="25" >:D</text> !-->
</g></svg>

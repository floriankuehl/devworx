import { Format } from './Format.js';
import { AutoRegistering } from './Elements.js';

export class ID extends AutoRegistering(HTMLSpanElement) {
	
  constructor() { 
    super()
  }
  
  static get baseTag(){ return 'span' }
  
  connectedCallback(){
    const 
      value = '' + parseInt( this.getAttribute('value') ),
      prefix = this.hasAttribute('prefix') ? this.getAttribute('prefix') : '',
      postfix = this.hasAttribute('postfix') ? this.getAttribute('postfix') : '',
      length = parseInt( this.getAttribute('length') )
    
    this.innerHTML = `${prefix}${value.padStart(length,'0')}${postfix}`
  }
}

export class Time extends AutoRegistering(HTMLTimeElement) {
  constructor() { 
    super()
  }
  
  static get baseTag(){ return 'time' }
   
  connectedCallback(){
    const dt = this.getAttribute('datetime')
    if( dt == '' || dt == 'null' || dt == '0000-00-00 00:00:00' ) 
      return;
    const d = new Date(dt)
    if( this.hasAttribute('dateonly') )
      this.innerHTML = Format.date(d)
    else
      this.innerHTML = Format.dateTime(d)
  }
}

export class Timespan extends AutoRegistering(HTMLTimeElement) {
  constructor() { 
    super()
  }
  
  static get baseTag(){ return 'time' }
  
  connectedCallback(){
    const time = this.timespan
    
    this.setAttribute('days',time[0])
    this.setAttribute('hours',time[1])
    this.setAttribute('minutes',time[2])
    this.setAttribute('seconds',time[3])
    
    const total = ( time[0] * 24 ) + time[1] + ( time[2] / 60 )
    this.setAttribute('total-hours',total)
    this.setAttribute('value',total)
    
    time[1] = (''+time[1]).padStart(2,'0')
    time[2] = (''+time[2]).padStart(2,'0')
    time[3] = (''+time[3]).padStart(2,'0')
    
    const segments = []
    if( time[0] > 0 ){
      const label = 'Tag' + ( time[0] > 1 ? 'e' : '' );
      segments.push(`${time[0]} ${label}`)
    }
    segments.push(`${time[1]}:${time[2]}`)
    this.innerHTML = segments.join(', ')
  }
  
  get timespan(){
    let
      dateFrom = this.getAttribute('from'),
      dateTo = this.getAttribute('to');

    if( dateFrom == '' || dateFrom == 'null' || dateFrom == '0000-00-00 00:00:00' )
      return [0,0,0,0]

    if( dateTo == '' || dateTo == 'null' || dateTo == '0000-00-00 00:00:00' )
      return [0,0,0,0]
    
    dateFrom = new Date(dateFrom)
    dateTo = new Date(dateTo)
    
    const 
      totalSeconds = Math.abs(dateTo - dateFrom) / 1000,
      totalMinutes = Math.floor(totalSeconds / 60),
      totalHours = Math.floor(totalMinutes / 60);
      
    const time = [
      Math.floor(totalHours / 24),
      totalHours % 24, 
      totalMinutes % 60, 
      totalSeconds % 60
    ]
    
    if( time[3] > 9 ){
      time[2]++
      time[3] = 0
      if( time[2] == 60 ){
        time[1]++
        time[2] = 0
      }
    }
    return time;
    
  }
}

export class Currency extends AutoRegistering(HTMLDataElement) {
  constructor() { 
    super()
  }
  
  static get baseTag(){ return 'data' }
  
  connectedCallback(){
    const value = parseFloat( this.getAttribute('value') );
    this.innerHTML = Format.currency(value);
  }
}

export class Salary extends Timespan {
  constructor() { 
    super()
  }
  
  static get baseTag(){ return 'data' }
  
  connectedCallback(){
    const 
      value = parseFloat( this.getAttribute('value') ),
      price = parseFloat( this.getAttribute('price') ),
      fix = this.getAttribute('fix') == '1'
    const total = fix ? value : value * price
    this.setAttribute('value',total)
    this.innerHTML = Format.currency(total);
  }
}

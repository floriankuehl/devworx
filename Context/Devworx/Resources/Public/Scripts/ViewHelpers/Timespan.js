import Format from '../Format.js'
import CustomElement from '../CustomElement.js'

export default class Timespan extends CustomElement(HTMLTimeElement) {
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
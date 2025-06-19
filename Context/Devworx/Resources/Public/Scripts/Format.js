export default class Format {
	
	static timestamp(v){
		return [
			[
				v.getFullYear(),
				( '' + ( v.getMonth()+1 ) ).padStart(2,'0'),
				( '' + v.getDate() ).padStart(2,'0')
			].join('-'),
			[
				( '' + v.getHours() ).padStart(2,'0'),
				( '' + v.getMinutes() ).padStart(2,'0'),
				( '' + v.getSeconds() ).padStart(2,'0')
			].join(':')
		].join(' ')
	}
	
	static dateTimeFormat = new Intl.DateTimeFormat(
		'de-DE',
		{
		  timeZone: 'Europe/Berlin', 
		  day: '2-digit', 
		  month: '2-digit',
		  year: 'numeric',
		  hour: '2-digit',
		  minute: '2-digit'
		}
	)
	
	static dateFormat = new Intl.DateTimeFormat(
		'de-DE',
		{
		  timeZone: 'Europe/Berlin', 
		  day: '2-digit', 
		  month: '2-digit',
		  year: 'numeric',
		  //hour: '2-digit',
		  //minute: '2-digit'
		}
	)
	
	static currencyFormat = new Intl.NumberFormat(
		'de-DE',
		{
		  style: 'currency', 
		  currency: 'EUR'
		}
	)
	
	static numberFormat = new Intl.NumberFormat(
		'de-DE',
		{
		  style: 'decimal',
		  minimumFractionDigits: 2,
		  maximumFractionDigits: 3
		}
	)
	
	static number(value){ return this.numberFormat.format(value) }
	static date(value){ return this.dateFormat.format(value) }
	static dateTime(value){ return this.dateTimeFormat.format(value) }
	static currency(value){ return this.currencyFormat.format(value) }
	static ucFirst(str){ return str.charAt(0).toUpperCase() + str.slice(1) }
	static nextQuarter(value){ return Math.ceil(value/900000) * 900000 }
	static lastQuarter(value){ return Math.floor(value/900000) * 900000 }
}

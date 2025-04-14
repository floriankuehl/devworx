export const Format = {
  timestamp: (v) => [
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
  ].join(' '),
  dateTimeFormat: new Intl.DateTimeFormat(
    'de-DE',
    {
      timeZone: 'Europe/Berlin', 
      day: '2-digit', 
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    }
  ),
  dateFormat: new Intl.DateTimeFormat(
    'de-DE',
    {
      timeZone: 'Europe/Berlin', 
      day: '2-digit', 
      month: '2-digit',
      year: 'numeric',
      //hour: '2-digit',
      //minute: '2-digit'
    }
  ),
  currencyFormat: new Intl.NumberFormat(
    'de-DE',
    {
      style: 'currency', 
      currency: 'EUR'
    }
  ),
  numberFormat: new Intl.NumberFormat(
    'de-DE',
    {
      style: 'decimal',
      minimumFractionDigits: 2,
      maximumFractionDigits: 3
    }
  ),
  number: function(value){ return this.numberFormat.format(value); },
  date: function(value){ return this.dateFormat.format(value) },
  dateTime: function(value){ return this.dateTimeFormat.format(value) },
  currency: function(value){ return this.currencyFormat.format(value) },
  ucFirst: function(str){ return str.charAt(0).toUpperCase() + str.slice(1) },
  nextQuarter: function(value){ return Math.ceil(value/900000) * 900000 },
  lastQuarter: function(value){ return Math.floor(value/900000) * 900000 }
};

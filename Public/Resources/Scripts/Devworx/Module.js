export * from './Format.js'
export * from './Api.js'
export * from './Elements.js'
export * from './ViewHelpers.js'

export const TagPrefix = 'devworx'

export const CustomElements = {
  app: ['BasicElement', 'div'],
  view: ['View', 'div'],
  list: ['List', 'div'],
  id: 'ID',
  time: ['Time','time'],
  timespan: 'Timespan',
  currency: 'Currency',
  salary: 'Salary',
  tabs: 'Tabs',
  dialog: ['Dialog','dialog']
}

export const Load = mod => {
  for( let k in CustomElements ){
    const v = CustomElements[k]
    if( Array.isArray(v) ){
      customElements.define( 
        `${TagPrefix}-${k}`, 
        mod[ v[0] ],
        {extends: mod[ v[1] ]}
      );
    } else {
      customElements.define( 
        `${TagPrefix}-${k}`, 
        mod[v]
      );
    }
  }
}

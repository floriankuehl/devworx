export * from './Format.js'
export * from './Api.js'

import * as Elements from './Elements.js'
import * as ViewHelpers from './ViewHelpers.js'
import * as ProjectElements from './ProjectElements.js'

function getBaseClass(constructor) {
  let proto = constructor.prototype;
  let prev = null;

  while (proto && proto !== HTMLElement.prototype) {
    prev = proto;
    proto = Object.getPrototypeOf(proto);
  }

  if (!prev) return null;
  return prev.constructor.name;
}

function registerCustomElements(module) {
  Object.keys(module).forEach((key) => {
    const value = module[key];
	
	if (
	  ( typeof value === 'function' ) &&
	  ( value.prototype instanceof HTMLElement ) &&
	  ( typeof value.register === 'function' ) &&
	  ( typeof value.elementTag === 'string' ) &&
	  ( !customElements.get(value.elementTag) )
	) {
		value.register();
		//console.log(`Added ${getBaseClass(value)} ${value.name} as ${value.elementTag}`);	
	}
  });
}

registerCustomElements(Elements)
registerCustomElements(ViewHelpers)
registerCustomElements(ProjectElements)

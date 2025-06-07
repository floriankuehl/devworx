export {default as Format} from './Format.js'
export {default as Api} from './Api.js'
export {default as CustomElement} from './CustomElement.js'

import * as Elements from './Elements.js'
import * as ViewHelpers from './ViewHelpers.js'
import * as ProjectElements from './ProjectElements.js'

export function getBaseClass(constructor) {
  let proto = constructor.prototype;
  let prev = null;

  while (proto && proto !== HTMLElement.prototype) {
    prev = proto;
    proto = Object.getPrototypeOf(proto);
  }

  if (!prev) return null;
  return prev.constructor.name;
}

export function registerCustomElements(module,debug=false) {
  Object.keys(module).forEach((key) => {
    const value = module[key];
	
	if (
	  ( typeof value === 'function' ) &&
	  ( value.prototype instanceof HTMLElement ) &&
	  ( typeof value.register === 'function' ) &&
	  ( typeof value.elementTag === 'string' ) &&
	  ( !customElements.get(value.elementTag) )
	) {
		const ok = value.register();
		if( debug )
			console.log(`Added ${getBaseClass(value)} ${value.name} as ${value.elementTag}`,ok);	
	}
  });
}

registerCustomElements(Elements)
registerCustomElements(ViewHelpers)
registerCustomElements(ProjectElements)

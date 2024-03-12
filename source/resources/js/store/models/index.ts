// const files = import.meta.glob('./*.ts', { eager: true });
//
// const modules = {};
//
// for (const key in files) {
//     if (key === './index.ts') {
//         continue;
//     }
//
//     const model = files[key].default;
//     modules[model.name || model.constructor.name] = model;
// }
//
// export default modules;

import Indicator from './indicator';

export default {
    Indicator
};

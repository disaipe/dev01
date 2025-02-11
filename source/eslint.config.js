import vuePug from '@42sol/eslint-plugin-vue-pug';
import antfu from '@antfu/eslint-config';

const config = antfu({
  ignores: [
    'app/**',
    'database/**',
    'docs/**',
    'storage/**',
    'node_modules/**',
    'public/**',
    'tests/**',
    'vendor/**',
  ],

  typescript: {
    overrides: {
      'ts/ban-ts-comment': ['off'],
      'ts/no-empty-object-type': ['off'],
      'ts/consistent-type-imports': ['error', {
        fixStyle: 'separate-type-imports',
        prefer: 'type-imports',
      }],
    },
  },

  stylistic: {
    semi: true,
  },

  vue: {
    overrides: {
      'vue/block-order': ['error', {
        order: ['template', 'script', 'style'],
      }],
    },

  },

  rules: {
    'unused-imports/no-unused-vars': ['off'],
    'unused-imports/no-unused-imports': ['off'],
  },
}).overrides({
  'antfu/vue/setup': {
    plugins: {
      'vue-pug': vuePug,
    },
  },
  'antfu/vue/rules': (configItem) => {
    configItem.languageOptions.parserOptions.templateTokenizer = {
      pug: 'vue-eslint-parser-template-tokenizer-pug',
    };

    configItem.rules = {
      ...configItem.rules,
      ...vuePug.configs.base.rules,
      'vue/valid-v-for': 'off',
      'vue/require-v-for-key': 'off',
    };

    return configItem;
  },
});

export default config;

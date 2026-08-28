import { cp, mkdir } from 'node:fs/promises';

const copies = [
  ['node_modules/bootstrap/dist/css/bootstrap.min.css', 'public/vendor/bootstrap/bootstrap.min.css'],
  ['node_modules/bootstrap/dist/js/bootstrap.bundle.min.js', 'public/vendor/bootstrap/bootstrap.bundle.min.js'],
  ['node_modules/bootstrap-icons/font/bootstrap-icons.min.css', 'public/vendor/bootstrap-icons/bootstrap-icons.min.css'],
  ['node_modules/bootstrap-icons/font/fonts', 'public/vendor/bootstrap-icons/fonts'],
  ['node_modules/aos/dist/aos.css', 'public/vendor/aos/aos.css'],
  ['node_modules/aos/dist/aos.js', 'public/vendor/aos/aos.js']
];

for (const [source, target] of copies) {
  await mkdir(target.substring(0, target.lastIndexOf('/')), { recursive: true });
  await cp(source, target, { recursive: true });
}

console.log('Bootstrap 5, Bootstrap Icons, and AOS assets copied to public/vendor.');

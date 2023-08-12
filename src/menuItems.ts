export type MenuItemProps = {
  to: string;
  name: string;
}

export const MainMenuItems: MenuItemProps[] = [
  {to: "/", name: 'Start'},
  {to: "/login", name: 'login'},
  {to: "/logout", name: 'logout'},
];

export const LoginMenuItems: MenuItemProps[] = [
  ...MainMenuItems,
  {to: "/startQuiz", name: 'Starta quiz'},
  {to: "/trackProgress", name: 'Följ andra'},
  {to: "/groups", name: 'Grupper'}
]
import React, { useReducer } from "react";
import { Link } from "react-router-dom";
import { Navbar, Container, DropdownButton, Dropdown, NavDropdown } from "react-bootstrap";

import { MainMenuItems, MenuItemProps, LoginMenuItems } from "../../menuItems";
import { appReducer, appState, AppState} from "../../state/reducers";
import { useCustomContext } from "../../state/custom.context";

type MenuProps = {
  items: MenuItemProps[]
}

function MenuItem (props: MenuItemProps) {
  return <>
    <li>
      <Link to={props.to}>{props.name}</Link>
    </li>
  </>;
}

function Menu(props: MenuProps) {
  return (
    <React.Fragment>
      <ul>
        {props.items.map(item=> <MenuItem to={item.to} name={item.name}  />)}
      </ul>
    </React.Fragment>
  )
}

export function Header() {
  const {state, dispatch} = useCustomContext();
  const {userName} = state;
  const items = userName ? LoginMenuItems : MainMenuItems;

  return <>
  <Navbar className="bg-body-tertiary">
    <Container>
      <Navbar.Toggle />
      <NavDropdown title="Kickoff 23">
        {items.map((itm, i)=>(
          <NavDropdown.Item key={i} href={itm.to}>{itm.name}</NavDropdown.Item>
        ))}
      </NavDropdown>
      <Navbar.Collapse className="justify-content-end">
        <Navbar.Text>
          {userName && "Inloggad som: "}
          <Link to="/login">{userName ? userName : 'logga in'}</Link>

        </Navbar.Text>
      </Navbar.Collapse>
    </Container>
  </Navbar>
  </>
}


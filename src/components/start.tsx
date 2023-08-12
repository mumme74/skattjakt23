
import React from "react";
import { Link } from "react-router-dom";

export function Start() {
  return <>
    <h1>StartSidan!</h1>
    <p>Detta är en webbapp snabbt ihopslängd för att kunna genomföra skattjakten 2023</p>
    <Link to="login">Logga in</Link>
  </>
}

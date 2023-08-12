import React, { useReducer, useState } from "react";
import { Group, User, appReducer, appState } from "../../state/reducers";
import { Badge, Button, Form } from "react-bootstrap";
import { useCustomContext } from "../../state/custom.context";



function OneGroup(props: {group: Group, userName: string}) {

  const [state, dispatch] = useReducer(appReducer, appState);


  return <div className="bg-gray border-sm1">
    <h4>{props.group.name}</h4>
    {props.group.creator===props.userName && (<Badge bg="secondary">Du äger denna grupp</Badge>)}
    {props.group.invites.indexOf(props.userName) > -1 && (
      <Button variant="primary" onClick={
        ()=>dispatch({type:'acceptInviteToGroup', payload:props.group.name})}
      >Acceptera inbjudan</Button>
    )}
    <h5>Medlemmar</h5>
    {props.group.members.map(member=>(<span>{member}</span>)).join(', ')}
    {props.group.invites.length && (
        <>
          <h5>Inbjudna</h5>
          {props.group.invites.map(member=>(<span>{member}</span>)).join(', ')}
        </>
     )}
  </div>
}


function NewGroup() {
  const {state, dispatch} = useCustomContext();
  const {userName, groups, allUsers} = state;

  const myGroup = groups.find(g=>g.creator===userName) ||
                  groups.find(g=>g.members.find(m=>m===userName));

  const invites = myGroup?.invites || [];
  const selectableUsers = allUsers.filter(p=>invites.indexOf(p.userName)<0);

  const inviteUser = (person: User) => {
    dispatch({type:'inviteToGroup', payload: {group: myGroup as Group, userName: person.userName}})
  }


  return <>
    <Form>
      {!myGroup ? (
        <Form.Group className="mb-3" controlId="groupName">
        <Form.Label>Gruppnamn</Form.Label>
        <Form.Control type="text" placeholder="Gruppens namn" />
        <Form.Text className="text-muted">
          Vad vill du din nya grupp skall heta?
        </Form.Text>
      </Form.Group>
      ):(
        <h3>{myGroup.name}</h3>
      )}
      {myGroup?.invites.length && (
        <>
          <h4>Inbjudna</h4>
          {myGroup.invites.map((member,i)=>(
            <Badge bg="secondary" key={i}>{member}</Badge>
           ))
          }
        </>
      )}
      {selectableUsers && (
        <>
          <h4>Bjud in</h4>
          {selectableUsers.map((p, i)=>(
            <>
              <Button variant="success" key={i} onClick={()=>inviteUser(p)}>{p.userName}</Button>
            </>
          ))}
        </>
      )}
    </Form>
  </>
}

export function Groups() {
  const {state, dispatch} = useCustomContext();
  const {userName, groups} = state;

  const [showNew, setShowNew] = useState(false);

  const newGroup = () =>{
    setShowNew(true);
  }

  const myGroup = groups.find(g=>g.creator===userName);

  return <>
    <h1>Grupper</h1>
    {!myGroup && !showNew && (
      <div>
        <Button variant="primary" onClick={newGroup}>Skapa ny</Button>
      </div>
    )}
    {showNew && <NewGroup />}
    {groups.map(group=><OneGroup group={group} userName={userName} />)}
  </>
}
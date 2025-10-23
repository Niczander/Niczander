"""
Secure Messaging Client
Phase 2 Project - Code Theory and Cryptography
Uses RSA for key exchange and AES for symmetric encryption
"""

from cryptography.hazmat.primitives.asymmetric import rsa, padding
from cryptography.hazmat.primitives import hashes, serialization
from cryptography.hazmat.primitives.ciphers import Cipher, algorithms, modes
from cryptography.hazmat.backends import default_backend
import os
import json
import base64
from datetime import datetime
import pickle


class User:
    """Represents a user with RSA key pair and active sessions"""
    
    def __init__(self, username):
        self.username = username
        print(f"\n Generating RSA-2048 key pair for {username}...")
        
        # Generate RSA key pair (2048-bit)
        self.private_key = rsa.generate_private_key(
            public_exponent=65537,
            key_size=2048,
            backend=default_backend()
        )
        self.public_key = self.private_key.public_key()
        self.sessions = {}  # {peer_username: session_key}
        
        # Extract key components for display
        private_numbers = self.private_key.private_numbers()
        public_numbers = self.public_key.public_numbers()
        
        self.key_info = {
            'public_exponent': public_numbers.e,
            'modulus': public_numbers.n,
            'private_exponent': private_numbers.d,
            'prime1': private_numbers.p,
            'prime2': private_numbers.q
        }
        
        print(f" Key generation complete for {username}")
        
    def get_public_key_pem(self):
        """Export public key in PEM format"""
        return self.public_key.public_bytes(
            encoding=serialization.Encoding.PEM,
            format=serialization.PublicFormat.SubjectPublicKeyInfo
        ).decode('utf-8')
    
    def save_keys_to_file(self, filepath):
        """Save user's keys to a file"""
        key_data = {
            'username': self.username,
            'public_key_pem': self.get_public_key_pem(),
            'private_key_pem': self.private_key.private_bytes(
                encoding=serialization.Encoding.PEM,
                format=serialization.PrivateFormat.PKCS8,
                encryption_algorithm=serialization.NoEncryption()
            ).decode('utf-8')
        }
        
        with open(filepath, 'w') as f:
            json.dump(key_data, f, indent=2)
        print(f"Keys saved to {filepath}")
    
    @classmethod
    def load_keys_from_file(cls, filepath):
        """Load user from saved keys file"""
        with open(filepath, 'r') as f:
            key_data = json.load(f)
        
        # Create user instance
        user = cls.__new__(cls)
        user.username = key_data['username']
        user.sessions = {}
        
        # Load private key
        user.private_key = serialization.load_pem_private_key(
            key_data['private_key_pem'].encode('utf-8'),
            password=None,
            backend=default_backend()
        )
        
        # Load public key
        user.public_key = serialization.load_pem_public_key(
            key_data['public_key_pem'].encode('utf-8'),
            backend=default_backend()
        )
        
        # Extract key components for display
        private_numbers = user.private_key.private_numbers()
        public_numbers = user.public_key.public_numbers()
        
        user.key_info = {
            'public_exponent': public_numbers.e,
            'modulus': public_numbers.n,
            'private_exponent': private_numbers.d,
            'prime1': private_numbers.p,
            'prime2': private_numbers.q
        }
        
        print(f"Keys loaded for user {user.username}")
        return user
    
    def get_key_info(self):
        """Get formatted key information for display"""
        return self.key_info
    
    def establish_session(self, peer_username, peer_public_key):
        """Establish a new session with a peer"""
        print(f"\n Establishing session with {peer_username}...")
        
        # Generate AES-256 session key
        session_key = os.urandom(32)  # 256 bits
        print(f" Generated AES-256 session key: {session_key.hex()[:32]}...")
        
        # Encrypt session key with peer's RSA public key
        print(f" Encrypting session key with {peer_username}'s RSA public key...")
        encrypted_session_key = peer_public_key.encrypt(
            session_key,
            padding.OAEP(
                mgf=padding.MGF1(algorithm=hashes.SHA256()),
                algorithm=hashes.SHA256(),
                label=None
            )
        )
        
        print(f" Encrypted session key (hex): {encrypted_session_key.hex()[:64]}...")
        
        # Store session key
        self.sessions[peer_username] = session_key
        
        return encrypted_session_key, session_key
    
    def decrypt_session_key(self, encrypted_session_key, peer_username):
        """Decrypt a session key received from a peer"""
        print(f"\n Decrypting session key from {peer_username}...")
        print(f" Received encrypted key (hex): {encrypted_session_key.hex()[:64]}...")
        
        # Check if this user has a private key
        if self.private_key is None:
            print(f"[!] Cannot decrypt session key - user '{self.username}' has no private key!")
            print("This user was imported with public key only and cannot receive messages.")
            raise ValueError(f"User {self.username} cannot decrypt - no private key available")
        
        session_key = self.private_key.decrypt(
            encrypted_session_key,
            padding.OAEP(
                mgf=padding.MGF1(algorithm=hashes.SHA256()),
                algorithm=hashes.SHA256(),
                label=None
            )
        )
        
        print(f" Decrypted session key: {session_key.hex()[:32]}...")
        
        # Store session key
        self.sessions[peer_username] = session_key
        return session_key
    
    def encrypt_message(self, message, peer_username):
        """Encrypt a message using AES-GCM with the session key"""
        if peer_username not in self.sessions:
            raise ValueError(f"No session established with {peer_username}")
        
        session_key = self.sessions[peer_username]
        
        print(f"\n Encrypting message with AES-256-GCM...")
        print(f" Using session key: {session_key.hex()[:32]}...")
        
        # Generate random IV (96 bits for GCM)
        iv = os.urandom(12)
        print(f" Generated IV (hex): {iv.hex()}")
        
        # Create AES-GCM cipher
        cipher = Cipher(
            algorithms.AES(session_key),
            modes.GCM(iv),
            backend=default_backend()
        )
        encryptor = cipher.encryptor()
        
        # Encrypt the message
        plaintext_bytes = message.encode('utf-8')
        print(f" Plaintext bytes (hex): {plaintext_bytes.hex()[:64]}...")
        
        ciphertext = encryptor.update(plaintext_bytes) + encryptor.finalize()
        tag = encryptor.tag
        
        print(f" Ciphertext (hex): {ciphertext.hex()[:64]}...")
        print(f" Authentication tag (hex): {tag.hex()}")
        
        # Return IV, ciphertext, and authentication tag
        return {
            'iv': base64.b64encode(iv).decode('utf-8'),
            'ciphertext': base64.b64encode(ciphertext).decode('utf-8'),
            'tag': base64.b64encode(tag).decode('utf-8'),
            'iv_hex': iv.hex(),
            'ciphertext_hex': ciphertext.hex(),
            'tag_hex': tag.hex()
        }
    
    def decrypt_message(self, encrypted_data, peer_username):
        """Decrypt a message using AES-GCM with the session key"""
        if peer_username not in self.sessions:
            raise ValueError(f"No session established with {peer_username}")
        
        session_key = self.sessions[peer_username]
        
        print(f"\n Decrypting message from {peer_username}...")
        print(f" Using session key: {session_key.hex()[:32]}...")
        print(f" IV (hex): {encrypted_data.get('iv_hex', 'N/A')}")
        print(f" Ciphertext (hex): {encrypted_data.get('ciphertext_hex', 'N/A')[:64]}...")
        print(f" Auth tag (hex): {encrypted_data.get('tag_hex', 'N/A')}")
        
        # Decode the encrypted data
        iv = base64.b64decode(encrypted_data['iv'])
        ciphertext = base64.b64decode(encrypted_data['ciphertext'])
        tag = base64.b64decode(encrypted_data['tag'])
        
        # Create AES-GCM cipher
        cipher = Cipher(
            algorithms.AES(session_key),
            modes.GCM(iv, tag),
            backend=default_backend()
        )
        decryptor = cipher.decryptor()
        
        # Decrypt the message
        plaintext_bytes = decryptor.update(ciphertext) + decryptor.finalize()
        print(f" Decrypted bytes (hex): {plaintext_bytes.hex()[:64]}...")
        
        return plaintext_bytes.decode('utf-8')


class SecureMessagingClient:
    """Main messaging client managing users and messages"""
    
    def __init__(self):
        self.users = {}  # {username: User object}
        self.messages = []  # List of all messages
    
    def create_user(self, username):
        """Create a new user with RSA key pair"""
        if username in self.users:
            print(f"[!] User '{username}' already exists!")
            return False
        
        user = User(username)
        self.users[username] = user
        
        # Display key information
        self._display_key_info(username)
        return True
    
    def _display_key_info(self, username):
        """Display detailed RSA key information"""
        user = self.users[username]
        key_info = user.get_key_info()
        
        print(f"\n{'='*80}")
        print(f"RSA KEY INFORMATION: {username}")
        print(f"{'='*80}")
        
        # Convert large numbers to strings for display
        modulus_str = str(key_info['modulus'])
        private_exp_str = str(key_info['private_exponent'])
        
        print(f"Public Exponent (e): {key_info['public_exponent']}")
        print(f"\nModulus (n):")
        print(f"  Length: {len(modulus_str)} digits")
        print(f"  First 50 digits: {modulus_str[:50]}...")
        print(f"  Last 50 digits: ...{modulus_str[-50:]}")
        
        print(f"\nPrivate Exponent (d):")
        print(f"  Length: {len(private_exp_str)} digits") 
        print(f"  First 50 digits: {private_exp_str[:50]}...")
        print(f"  Last 50 digits: ...{private_exp_str[-50:]}")
        
        print(f"\nPrime 1 (p): {len(str(key_info['prime1']))} digits")
        print(f"Prime 2 (q): {len(str(key_info['prime2']))} digits")
        
        print(f"\nPublic Key (PEM):")
        pem = user.get_public_key_pem()
        print(pem[:100] + "..." if len(pem) > 100 else pem)
        print("="*80)
    
    def send_message(self, sender_username, recipient_username, message):
        """Send an encrypted message from sender to recipient"""
        if sender_username not in self.users:
            print(f"[!] Sender '{sender_username}' not found!")
            return False
        
        if recipient_username not in self.users:
            print(f"[!] Recipient '{recipient_username}' not found!")
            return False
        
        sender = self.users[sender_username]
        recipient = self.users[recipient_username]
        
        # Check if recipient can receive messages (has private key)
        if recipient.private_key is None:
            print(f"[!] Cannot send message to '{recipient_username}'!")
            print("Recipient was imported with public key only and cannot decrypt messages.")
            print("The recipient must create their own account to receive messages.")
            return False
        
        print(f"\n{'='*80}")
        print(f"SENDING MESSAGE: {sender_username} → {recipient_username}")
        print(f"{'='*80}")
        print(f" Plaintext: {message}")
        
        # Check if session exists
        session_established = False
        if recipient_username not in sender.sessions:
            print(f"\n No existing session found. Establishing new session...")
            # Establish new session
            encrypted_session_key, session_key = sender.establish_session(
                recipient_username, 
                recipient.public_key
            )
            
            # Recipient decrypts and stores the session key
            recipient.decrypt_session_key(encrypted_session_key, sender_username)
            
            session_established = True
            print(f" New session key established between {sender_username} and {recipient_username}")
        else:
            print(f"\n Using existing session key")
        
        # Encrypt message with AES
        encrypted_message = sender.encrypt_message(message, recipient_username)
        
        # Store message
        msg_record = {
            'from': sender_username,
            'to': recipient_username,
            'plaintext': message,
            'encrypted': encrypted_message,
            'session_established': session_established,
            'timestamp': datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        }
        self.messages.append(msg_record)
        
        print(f"\n Message encrypted and sent successfully!")
        print(f"{'='*80}")
        
        return True
    
    def view_messages(self):
        """Display all messages with detailed cryptographic information"""
        if not self.messages:
            print("\n[!] No messages yet.")
            return
        
        print("\n" + "="*100)
        print("MESSAGE HISTORY - CRYPTOGRAPHIC DETAILS")
        print("="*100)
        
        for i, msg in enumerate(self.messages, 1):
            print(f"\n{'━'*100}")
            print(f"📨 MESSAGE #{i}")
            print(f"{'━'*100}")
            print(f"From: {msg['from']} → To: {msg['to']}")
            print(f"Time: {msg['timestamp']}")
            
            if msg['session_established']:
                print(" [NEW SESSION KEY EXCHANGED VIA RSA]")
            
            print(f"\n PLAINTEXT:")
            print(f"   {msg['plaintext']}")
            
            enc = msg['encrypted']
            print(f"\n ENCRYPTED DATA:")
            print(f"   IV (Base64): {enc['iv']}")
            print(f"   IV (Hex):    {enc['iv_hex']}")
            print(f"   Ciphertext (Base64): {enc['ciphertext'][:80]}...")
            print(f"   Ciphertext (Hex):    {enc['ciphertext_hex'][:80]}...")
            print(f"   Auth Tag (Base64): {enc['tag']}")
            print(f"   Auth Tag (Hex):    {enc['tag_hex']}")
            
            print(f"\nENCRYPTION INFO:")
            plaintext_len = len(msg['plaintext'])
            ciphertext_len = len(enc['ciphertext'])
            print(f"   Plaintext length:  {plaintext_len} characters")
            print(f"   Ciphertext length: {ciphertext_len} Base64 characters")
            print(f"   Expansion: {ciphertext_len/plaintext_len:.2f}x (due to Base64 encoding)")
    
    def verify_message(self, message_index):
        """Decrypt and verify a message"""
        if message_index < 0 or message_index >= len(self.messages):
            print("[!] Invalid message index!")
            return
        
        msg = self.messages[message_index]
        recipient = self.users[msg['to']]
        
        print(f"\n{'='*80}")
        print(f"VERIFYING MESSAGE #{message_index + 1}")
        print(f"{'='*80}")
        
        try:
            decrypted = recipient.decrypt_message(msg['encrypted'], msg['from'])
            print(f"\n Message decrypted successfully!")
            print(f"Decrypted text: {decrypted}")
            print(f"Original text:  {msg['plaintext']}")
            print(f"Match: {decrypted == msg['plaintext']}")
        except Exception as e:
            print(f"Decryption failed: {e}")
        
        print("="*80)
    
    def show_user_info(self, username):
        """Display user's cryptographic information"""
        if username not in self.users:
            print(f"[!] User '{username}' not found!")
            return
        
        user = self.users[username]
        
        print(f"\n{'='*80}")
        print(f"USER INFORMATION: {username}")
        print(f"{'='*80}")
        
        self._display_key_info(username)
        
        print(f"\nACTIVE SESSIONS:")
        if user.sessions:
            for peer, session_key in user.sessions.items():
                print(f"  • {peer}: AES-256 key = {session_key.hex()[:32]}...")
        else:
            print("  No active sessions")
        print("="*80)
    
    def export_message_package(self, message_index, filepath):
        """Export an encrypted message with sender's public key for sharing"""
        if message_index < 0 or message_index >= len(self.messages):
            print("[!] Invalid message index!")
            return False
        
        msg = self.messages[message_index]
        sender = self.users[msg['from']]
        recipient = self.users[msg['to']]
        
        # Get the session key for this conversation
        session_key = sender.sessions.get(msg['to'])
        if not session_key:
            print("[!] No session key found for this conversation!")
            return False
        
        # Encrypt session key with recipient's public key
        encrypted_session_key = recipient.public_key.encrypt(
            session_key,
            padding.OAEP(
                mgf=padding.MGF1(algorithm=hashes.SHA256()),
                algorithm=hashes.SHA256(),
                label=None
            )
        )
        
        # Create shareable package
        package = {
            'format_version': '1.1',
            'sender_username': msg['from'],
            'sender_public_key': sender.get_public_key_pem(),
            'recipient_username': msg['to'],
            'encrypted_message': msg['encrypted'],
            'encrypted_session_key': base64.b64encode(encrypted_session_key).decode('utf-8'),
            'timestamp': msg['timestamp'],
            'session_established': msg['session_established']
        }
        
        with open(filepath, 'w') as f:
            json.dump(package, f, indent=2)
        
        print(f"\n Message package exported to: {filepath}")
        print(f"This file contains both the encrypted message AND the session key.")
        print(f"It can be shared with {msg['to']} for complete decryption.")
        return True
    
    def import_message_package(self, filepath, recipient_username):
        """Import and decrypt a shared message package"""
        try:
            with open(filepath, 'r') as f:
                package = json.load(f)
            
            if recipient_username not in self.users:
                print(f"[!] Recipient '{recipient_username}' not found!")
                print("Please create the recipient user first.")
                return False
            
            recipient = self.users[recipient_username]
            
            # Check if recipient has private key (needed for decryption)
            if recipient.private_key is None:
                print(f"[!] User '{recipient_username}' doesn't have a private key!")
                print("You can only decrypt messages with your own private key.")
                return False
            
            # Verify this message is for the correct recipient
            if package['recipient_username'] != recipient_username:
                print(f"[!] Message is for '{package['recipient_username']}', not '{recipient_username}'")
                return False
            
            print(f"\n{'='*80}")
            print(f"IMPORTING SHARED MESSAGE")
            print(f"{'='*80}")
            print(f"From: {package['sender_username']}")
            print(f"To: {package['recipient_username']}")
            print(f"Timestamp: {package['timestamp']}")
            print(f"Package format: {package.get('format_version', '1.0')}")
            
            # Load sender's public key
            sender_public_key = serialization.load_pem_public_key(
                package['sender_public_key'].encode('utf-8'),
                backend=default_backend()
            )
            
            # Create temporary sender user for session establishment
            if package['sender_username'] not in self.users:
                temp_sender = User.__new__(User)
                temp_sender.username = package['sender_username']
                temp_sender.public_key = sender_public_key
                temp_sender.sessions = {}
                self.users[package['sender_username']] = temp_sender
                print(f"\n Created temporary user profile for {package['sender_username']}")
            
            # Handle session key based on package format
            if 'encrypted_session_key' in package:
                # New format (v1.1+) - session key included in package
                print(f"\n Decrypting session key from package...")
                encrypted_session_key = base64.b64decode(package['encrypted_session_key'])
                
                # Decrypt session key with recipient's private key
                session_key = recipient.private_key.decrypt(
                    encrypted_session_key,
                    padding.OAEP(
                        mgf=padding.MGF1(algorithm=hashes.SHA256()),
                        algorithm=hashes.SHA256(),
                        label=None
                    )
                )
                
                # Store session key
                recipient.sessions[package['sender_username']] = session_key
                print(f" Session key decrypted and stored for {package['sender_username']}")
                
            else:
                # Old format (v1.0) - check for existing session
                if package['session_established']:
                    print("\n[!] This is an old format message that established a new session.")
                    print("The session key was not included in the package.")
                    print("Please use the new export format or establish a session first.")
                    return False
                
                # Check if we have an existing session
                if package['sender_username'] not in recipient.sessions:
                    print(f"\n[!] No session key found for {package['sender_username']}")
                    print("You need to establish a session first or use a newer message package format.")
                    return False
            
            # Decrypt the message
            decrypted = recipient.decrypt_message(
                package['encrypted_message'], 
                package['sender_username']
            )
            
            print(f"\n✅ MESSAGE DECRYPTED SUCCESSFULLY!")
            print(f"Decrypted message: {decrypted}")
            
            # Add to local message history
            msg_record = {
                'from': package['sender_username'],
                'to': package['recipient_username'],
                'plaintext': decrypted,
                'encrypted': package['encrypted_message'],
                'session_established': package['session_established'],
                'timestamp': package['timestamp'],
                'imported': True
            }
            self.messages.append(msg_record)
            
            print(f"Message added to local history.")
            print(f"{'='*80}")
            return True
            
        except FileNotFoundError:
            print(f"[!] File not found: {filepath}")
            return False
        except json.JSONDecodeError:
            print(f"[!] Invalid message package format")
            return False
        except Exception as e:
            print(f"[!] Error importing message: {e}")
            return False
    
    def export_public_key(self, username, filepath):
        """Export a user's public key for sharing"""
        if username not in self.users:
            print(f"[!] User '{username}' not found!")
            return False
        
        user = self.users[username]
        key_data = {
            'username': username,
            'public_key_pem': user.get_public_key_pem(),
            'exported_at': datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        }
        
        with open(filepath, 'w') as f:
            json.dump(key_data, f, indent=2)
        
        print(f"\n Public key for '{username}' exported to: {filepath}")
        return True
    
    def import_public_key(self, filepath):
        """Import someone's public key"""
        try:
            with open(filepath, 'r') as f:
                key_data = json.load(f)
            
            username = key_data['username']
            
            if username in self.users:
                print(f"[!] User '{username}' already exists!")
                return False
            
            # Create user with just public key
            user = User.__new__(User)
            user.username = username
            user.sessions = {}
            
            # Load public key
            user.public_key = serialization.load_pem_public_key(
                key_data['public_key_pem'].encode('utf-8'),
                backend=default_backend()
            )
            
            # No private key for imported users
            user.private_key = None
            
            # Extract key components for display
            public_numbers = user.public_key.public_numbers()
            user.key_info = {
                'public_exponent': public_numbers.e,
                'modulus': public_numbers.n,
                'private_exponent': None,
                'prime1': None,
                'prime2': None
            }
            
            self.users[username] = user
            
            print(f"\n✅ Public key imported for user: {username}")
            print(f"Exported at: {key_data['exported_at']}")
            print(f"You can now send encrypted messages to {username}")
            return True
            
        except FileNotFoundError:
            print(f"[!] File not found: {filepath}")
            return False
        except Exception as e:
            print(f"[!] Error importing public key: {e}")
            return False
    

def main():
    """Main function with interactive demo"""
    client = SecureMessagingClient()
    
    print("="*80)
    print("SECURE MESSAGING CLIENT")
    print("RSA-2048 Key Exchange + AES-256-GCM Encryption")
    print("="*80)
    
    # Interactive mode
    while True:
        print("\nOptions:")
        print("1. Create user")
        print("2. Send message")
        print("3. View messages")
        print("4. Verify message")
        print("5. Show user info")
        print("6. Demo mode")
        print("7. Save user keys")
        print("8. Load user keys")
        print("9. Export public key")
        print("10. Import public key")
        print("11. Export message package")
        print("12. Import message package")
        print("13. Exit")
        
        choice = input("\nEnter choice (1-13): ").strip()
        
        if choice == '1':
            username = input("Enter username: ").strip()
            client.create_user(username)
        
        elif choice == '2':
            sender = input("Sender username: ").strip()
            recipient = input("Recipient username: ").strip()
            message = input("Message: ").strip()
            client.send_message(sender, recipient, message)
        
        elif choice == '3':
            client.view_messages()
        
        elif choice == '4':
            try:
                idx = int(input("Message index (0-based): ").strip())
                client.verify_message(idx)
            except ValueError:
                print("[!] Invalid index!")
        
        elif choice == '5':
            username = input("Username: ").strip()
            client.show_user_info(username)
        
        elif choice == '6':
            print("\n Starting demo mode...")
            # Create demo users
            client.create_user("Alice")
            client.create_user("Bob")
            
            # Send demo messages
            client.send_message("Alice", "Bob", "Hello Bob! This is our first secure message.")
            client.send_message("Bob", "Alice", "Hi Alice! The encryption is working perfectly.")
            client.send_message("Alice", "Bob", "Notice how the session key is reused for efficiency.")
            
            # View all messages
            client.view_messages()
        
        elif choice == '7':
            username = input("Username to save: ").strip()
            if username in client.users:
                filepath = input("Save to file (e.g., alice_keys.json): ").strip()
                client.users[username].save_keys_to_file(filepath)
            else:
                print(f"[!] User '{username}' not found!")
        
        elif choice == '8':
            filepath = input("Load keys from file: ").strip()
            try:
                user = User.load_keys_from_file(filepath)
                if user.username not in client.users:
                    client.users[user.username] = user
                    print(f"User '{user.username}' loaded successfully!")
                else:
                    print(f"[!] User '{user.username}' already exists!")
            except Exception as e:
                print(f"[!] Error loading keys: {e}")
        
        elif choice == '9':
            username = input("Username to export public key: ").strip()
            filepath = input("Export to file (e.g., alice_public.json): ").strip()
            client.export_public_key(username, filepath)
        
        elif choice == '10':
            filepath = input("Import public key from file: ").strip()
            client.import_public_key(filepath)
        
        elif choice == '11':
            try:
                idx = int(input("Message index to export (0-based): ").strip())
                filepath = input("Export to file (e.g., message_package.json): ").strip()
                client.export_message_package(idx, filepath)
            except ValueError:
                print("[!] Invalid index!")
        
        elif choice == '12':
            filepath = input("Import message package from file: ").strip()
            recipient = input("Your username (recipient): ").strip()
            client.import_message_package(filepath, recipient)
        
        elif choice == '13':
            print("\n Secure Messaging Client shutdown complete!")
            break
        
        else:
            print("[!] Invalid choice!")


if __name__ == "__main__":
    main()
package pt.ipleiria.estg.dei.amsi.cinelive.activities;

import android.os.Bundle;
import android.widget.Toast;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ActivityEditarPerfilBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.UserValidationListener;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.AuthManager;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.PerfilManager;
import pt.ipleiria.estg.dei.amsi.cinelive.models.User;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ConnectionUtils;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ErrorUtils;

public class EditarPerfilActivity extends AppCompatActivity {

    ActivityEditarPerfilBinding binding;
    PerfilManager perfilManager;
    User original;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);

        binding = ActivityEditarPerfilBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        setSupportActionBar(binding.toolbar.topAppBar);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        getSupportActionBar().setTitle(R.string.btn_editar_perfil);
        binding.form.tilPassword.setHint(R.string.form_hint_password_opcional);

        // Obter o perfil manager
        perfilManager = PerfilManager.getInstance();

        // Obter dados originais
        original = perfilManager.getCache();

        // Carregar perfil e configurar listeners
        setFields(original);
        setOnClickListeners();
    }

    private User getEdited() {
        String username = String.valueOf(binding.form.etUsername.getText());
        String password = String.valueOf(binding.form.etPassword.getText());
        String email = String.valueOf(binding.form.etEmail.getText());
        String nome = String.valueOf(binding.form.etNome.getText());
        String telemovel = String.valueOf(binding.form.etTelemovel.getText());

        // Devolver objeto user
        return new User(username, password, email, nome, telemovel);
    }

    private void setFields(User perfil) {
        binding.form.etUsername.setText(perfil.getUsername());
        binding.form.etEmail.setText(perfil.getEmail());
        binding.form.etNome.setText(perfil.getNome());
        binding.form.etTelemovel.setText(perfil.getTelemovel());
    }

    private void setOnClickListeners() {
        // Botão guardar
        binding.btnGuardar.setOnClickListener(v -> {
            // Verificar se tem internet
            if (!ConnectionUtils.hasInternet(this)) {
                ErrorUtils.showToast(this, ErrorUtils.Type.NO_INTERNET);
                return;
            }

            // Obter e validar novos dados
            User edited = getEdited();
            if (!validateFields(edited)) return;

            // Atualizar perfil
            updatePerfil(edited);
        });

        // Botão cancelar
        binding.btnCancelar.setOnClickListener(v -> {
            finish();
        });
    }

    private void updatePerfil(User edited) {
        perfilManager.updatePerfil(this, original, edited, new UserValidationListener() {
            @Override
            public void onSuccess() {
                Toast.makeText(getApplicationContext(), R.string.msg_sucesso_editar_perfil, Toast.LENGTH_SHORT).show();
                finish();
            }
            @Override
            public void onUsernameTaken() {
                binding.form.tilUsername.setError(getString(R.string.msg_username_indisponivel));
            }
            @Override
            public void onEmailTaken() {
                binding.form.tilEmail.setError(getString(R.string.msg_email_indisponivel));
            }
            @Override
            public void onError() {
                Toast.makeText(getApplicationContext(), R.string.msg_erro_editar_perfil, Toast.LENGTH_LONG).show();
            }
        });
    }

    private boolean validateFields(User user) {
        boolean isValid = true;
        int minLengthUsername = AuthManager.MIN_LENGTH_USERNAME;
        int minLengthPassword = AuthManager.MIN_LENGTH_PASSWORD;
        int minLengthTelemovel = AuthManager.MIN_LENGTH_TELEMOVEL;

        // Validar username
        if (user.getUsername().length() < minLengthUsername) {
            binding.form.tilUsername.setError(getString(R.string.msg_min_caracteres, minLengthUsername));
            isValid = false;
        } else binding.form.tilUsername.setErrorEnabled(false);

        // Validar password
        if (!user.getPassword().isEmpty() && user.getPassword().length() < minLengthPassword) {
            binding.form.tilPassword.setError(getString(R.string.msg_min_caracteres, minLengthPassword));
            isValid = false;
        } else binding.form.tilPassword.setErrorEnabled(false);

        // Validar email
        if (user.getEmail().isEmpty()) {
            binding.form.tilEmail.setError(getString(R.string.msg_campo_obrigatorio));
            isValid = false;
        } else if (!android.util.Patterns.EMAIL_ADDRESS.matcher(user.getEmail()).matches()) {
            binding.form.tilEmail.setError(getString(R.string.msg_email_invalido));
            isValid = false;
        } else binding.form.tilEmail.setErrorEnabled(false);

        // Validar nome
        if (user.getNome().isEmpty()) {
            binding.form.tilNome.setError(getString(R.string.msg_campo_obrigatorio));
            isValid = false;
        } else binding.form.tilNome.setErrorEnabled(false);

        // Validar telemóvel
        if (user.getTelemovel().length() < 9) {
            binding.form.tilTelemovel.setError(getString(R.string.msg_min_caracteres, minLengthTelemovel));
            isValid = false;
        } else binding.form.tilTelemovel.setErrorEnabled(false);

        return isValid;
    }

    @Override
    public boolean onSupportNavigateUp() {
        finish();
        return true;
    }
}
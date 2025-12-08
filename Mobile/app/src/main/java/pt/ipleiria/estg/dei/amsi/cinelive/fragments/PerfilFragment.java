package pt.ipleiria.estg.dei.amsi.cinelive.fragments;

import android.content.Intent;
import android.os.Bundle;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;

import android.view.LayoutInflater;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import android.view.View;
import android.view.ViewGroup;
import android.widget.EditText;
import android.widget.Toast;

import com.google.android.material.dialog.MaterialAlertDialogBuilder;

import pt.ipleiria.estg.dei.amsi.cinelive.activities.ConfiguracoesActivity;
import pt.ipleiria.estg.dei.amsi.cinelive.activities.EditarPerfilActivity;
import pt.ipleiria.estg.dei.amsi.cinelive.R;
import pt.ipleiria.estg.dei.amsi.cinelive.activities.MainActivity;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.FragmentPerfilBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.PerfilListener;
import pt.ipleiria.estg.dei.amsi.cinelive.listeners.StandardListener;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.AuthManager;
import pt.ipleiria.estg.dei.amsi.cinelive.managers.PerfilManager;
import pt.ipleiria.estg.dei.amsi.cinelive.models.User;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ConnectionUtils;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.ErrorPage;

public class PerfilFragment extends Fragment {
    private FragmentPerfilBinding binding;
    private AuthManager authManager;
    private PerfilManager perfilManager;


    @Override
    public void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setHasOptionsMenu(true);

        // Obter o auth manager
        authManager = AuthManager.getInstance();

        // Obter o perfil manager
        perfilManager = PerfilManager.getInstance();
    }

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        binding = FragmentPerfilBinding.inflate(inflater, container, false);
        return binding.getRoot();
    }

    @Override
    public void onCreateOptionsMenu(Menu menu, MenuInflater inflater) {
        if (ConnectionUtils.hasInternet(requireContext())) {
            inflater.inflate(R.menu.menu_configuracoes, menu);
            super.onCreateOptionsMenu(menu, inflater);
        }
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        if (item.getItemId() == R.id.itemConfiguracoes) {
            startActivity(new Intent(getActivity(), ConfiguracoesActivity.class));
            return true;
        }

        return super.onOptionsItemSelected(item);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        disableFields();
        binding.mainFlipper.setDisplayedChild(0); // Main Loading

        // Carregar perfil
        loadPerfil();

        // Swipe refresh
        binding.swipeRefresh.setOnRefreshListener(() -> {
            binding.swipeRefresh.setRefreshing(false);

            // Apenas limpar a cache se tiver internet
            if (ConnectionUtils.hasInternet(requireContext())) perfilManager.clearCache();

            // Carregar perfil
            loadPerfil();
        });

        // Botão Editar Perfil
        binding.btnEditarPerfil.setOnClickListener(v -> {
            // Verificar se tem internet
            if (!ConnectionUtils.hasInternet(requireContext())) {
                Toast.makeText(requireContext(), R.string.erro_internet_titulo, Toast.LENGTH_SHORT).show();
                return;
            }

            Intent intent = new Intent(getActivity(), EditarPerfilActivity.class);
            startActivity(intent);
        });

        // Botão Eliminar Conta
        binding.btnEliminarConta.setOnClickListener(v -> {
            // Verificar se tem internet
            if (!ConnectionUtils.hasInternet(requireContext())) {
                Toast.makeText(requireContext(), R.string.erro_internet_titulo, Toast.LENGTH_SHORT).show();
                return;
            }

            new MaterialAlertDialogBuilder(v.getContext())
                .setTitle(R.string.btn_eliminar_conta)
                .setMessage(R.string.msg_eliminar_conta)
                .setPositiveButton(R.string.btn_eliminar_conta, (dialog, which) -> {
                    perfilManager.deleteAccount(requireContext(), new StandardListener() {
                        @Override
                        public void onSuccess() {
                            Toast.makeText(requireContext(), R.string.msg_conta_eliminada, Toast.LENGTH_SHORT).show();
                            resetActivity();
                        }

                        @Override
                        public void onError() {
                            Toast.makeText(requireContext(), R.string.msg_erro_eliminar_conta, Toast.LENGTH_SHORT).show();
                            resetActivity();
                        }
                    });
                }).setNegativeButton(R.string.btn_cancelar, null).show();
        });

        // Botão Logout
        binding.btnLogout.setOnClickListener(v -> {
            authManager.logout(requireContext());
            resetActivity();
        });
    }

    private void loadPerfil() {
        binding.mainFlipper.setDisplayedChild(0); // Main Loading

        // Obter estado da ligação à internet
        boolean hasInternet = ConnectionUtils.hasInternet(requireContext());

        perfilManager.fetchPerfil(requireContext(), new PerfilListener() {
            @Override
            public void onSuccess(User perfil) {
                setFields(perfil);

                // Tem cache mas não tem internet
                if (!hasInternet) {
                    Toast.makeText(requireActivity(), R.string.erro_internet_titulo, Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onError() {
                showError(hasInternet ? ErrorPage.Type.TOKEN_INVALIDO : ErrorPage.Type.INTERNET);
            }
        });

    }

    private void setFields(User perfil) {
        binding.mainFlipper.setDisplayedChild(2); // Main Content

        if (perfil != null) {
            binding.form.etUsername.setText(perfil.getUsername());
            binding.form.etEmail.setText(perfil.getEmail());
            binding.form.etNome.setText(perfil.getNome());
            binding.form.etTelemovel.setText(perfil.getTelemovel());
        }
    }

    private void showError(ErrorPage.Type type) {
        // Evitar crash ao sair do fragment
        if (binding == null || !isAdded()) return;

        binding.mainFlipper.setDisplayedChild(1); // Error
        ErrorPage.showError(binding.mainError, type);

        // Action do botão
        binding.mainError.btnAction.setOnClickListener(v -> {
            switch (type) {
                case INTERNET:
                    loadPerfil();
                    break;
                case TOKEN_INVALIDO:
                    authManager.logout(requireContext());
                    resetActivity();
                    break;
            }
        });
    }

    private void resetActivity() {
        Intent intent = new Intent(requireContext(), MainActivity.class);
        intent.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP | Intent.FLAG_ACTIVITY_NEW_TASK);
        startActivity(intent);

        requireActivity().finish();
    }

    private void disableFields() {
        EditText[] fields = {
                binding.form.etUsername, binding.form.etEmail, binding.form.etNome, binding.form.etTelemovel
        };

        for (EditText et : fields) {
            et.setKeyListener(null);
            et.setCursorVisible(false);
            et.setFocusable(false);
        }

        binding.form.tilPassword.setVisibility(View.GONE);
    }

    @Override
    public void onResume() {
        super.onResume();

        // Carregar perfil
        if (perfilManager.getPerfil() == null) loadPerfil();
    }

    @Override
    public void onDestroyView() {
        super.onDestroyView();
        binding = null;
    }
}